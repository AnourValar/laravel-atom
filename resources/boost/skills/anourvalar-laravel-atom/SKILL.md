---
name: anourvalar-laravel-atom
description: Load when working in a Laravel project using anourvalar/laravel-atom (the Atom facade) for ACID workflows - transaction commit/rollback hooks, pessimistic/optimistic advisory locks (lockUser/lockOrder via __call), Mapper/MapperCollection DTOs cast on Eloquent models, Repository helpers, daemon loops, Redis exchanger, or its provided HTTP/Job middlewares and helper services.
---

# AnourValar Laravel Atom

`anourvalar/laravel-atom` is a "ACID API" toolkit for Laravel. It centers on the `Atom` facade for transaction-aware callbacks (`onCommit`, `onRollBack`, `beforeCommit`), named distributed locks with pluggable strategies (advisory or table-based, pessimistic or optimistic), strict DTOs (`Mapper`, `MapperCollection`) usable as Eloquent casts, and a grab-bag of helpers (string/number/date/array), middlewares, traits, and a global exception renderer auto-registered by the service provider.

## When to use

- The project's `composer.json` requires `anourvalar/laravel-atom`, or code references `\Atom::`, `AtomFacade`, `\AnourValar\LaravelAtom\...`.
- You need to schedule work after a DB transaction commits or rolls back (`Atom::onCommit`, `Atom::onRollBack`, `Atom::beforeCommit`).
- You need named application locks to prevent race conditions (`Atom::lock`, `Atom::lockUser($id)`, `Atom::strategy('optimistic_advisory')->lock(...)`).
- You are designing DTOs / value objects that should also serve as Eloquent JSON casts (extend `Mapper` or `MapperCollection`).
- You want to use the package's helpers (`DateHelper`, `NumberHelper`, `StringHelper`, `ArrayHelper`, `LayoutHelper`) or middlewares.
- You are writing a long-running command and want the package's `daemon()` loop, or a Redis-backed `exchangerPush/Pull`.
- You are implementing analytics endpoints via `AnalyticsInterface` + `AnalyticsTrait`.

## Setup notes

- Auto-discovered: provider `AnourValar\LaravelAtom\Providers\LaravelAtomServiceProvider`, alias `Atom => AnourValar\LaravelAtom\Facades\AtomFacade`.
- Configs published via `--tag=config`: `config/atom.php` (locks, number multiple, timezone_client, fulltext_locale) and `config/bindings.php` (auto-bound singletons declared with `['bind' => Implementation::class]` entries).
- The `pessimistic_transaction` / `optimistic_transaction` strategies need the published `locks` table migration (in `database/migrations/`). The default `pessimistic_advisory` strategy is PostgreSQL-only and needs no table.
- The provider auto-listens to `TransactionCommitting`, `TransactionCommitted`, `TransactionRolledBack` and dispatches them into the Atom service.
- The provider also installs `renderable()` JSON handlers for many Laravel exceptions (Throttle, NotFound/ModelNotFound, InvalidSignature, MethodNotAllowed, Authorization, Authentication, TokenMismatch, MassAssignment, malformed UTF-8). Be aware these are global side effects.

## Facades

### `AnourValar\LaravelAtom\Facades\AtomFacade` (alias `Atom`)

Resolves to the singleton `AnourValar\LaravelAtom\Service` (see Services). All methods documented below in the Service section are callable as `Atom::method(...)`.

The facade exposes a magic `__call` pattern: any method starting with `lock` is converted to a lock on a snake_cased entity name. `Atom::lockUserOrder($id)` is equivalent to `Atom::lock('user_order', $id)`.

```php
use Atom; // or use AnourValar\LaravelAtom\Facades\AtomFacade as Atom;

DB::transaction(function () use ($user, $order) {
    Atom::lockUser($user->id);              // pessimistic advisory lock on ('user', $user->id)
    Atom::onCommit(fn () => dispatch(new SendReceipt($order)));
    Atom::onRollBack(fn () => Storage::delete($order->draft_path));
    // ... business logic ...
});
```

## Services

### `AnourValar\LaravelAtom\Service`

The class behind the `Atom` facade. Bound as singleton.

- `strategy(string $strategy): self` - return a new Service instance using a different lock strategy key (`pessimistic_advisory`, `optimistic_advisory`, `pessimistic_transaction`, `optimistic_transaction`).
- `lock(...$args): void` - apply a lock keyed by the json-encoded normalized args. Logs a warning if it took longer than `atom.locks.warning_wait_seconds`. Magic: `lock<Entity>($id)` via `__call`.
- `transactionZeroLevel(int $level): void` - shift what "outermost transaction" means; useful inside test cases that wrap each test in a transaction.
- `beforeCommit(callable $closure, ?string $connection = null, ?string $uniqueName = null): int|string|null` - run on `TransactionCommitting`. Runs immediately if not inside a transaction. Returns the registry key (or `null` if executed immediately).
- `onCommit(callable $closure, ?string $connection = null, ?string $uniqueName = null): int|string|null` - run on `TransactionCommitted`. Same semantics. If `$uniqueName` is reused, the previous callback for that name is replaced.
- `onRollBack(callable $closure, ?string $connection = null, ?string $uniqueName = null): int|string|null` - run on `TransactionRolledBack`. Skipped silently if not in a transaction.
- `removeEvent(string $event, int|string $key, ?string $connection = null): void` - cancel a previously-registered callback. `$event` is one of `before_commit`, `commit`, `rollback`.
- `triggerTransaction($event): void` - internal hook invoked by the provider's event listener. Do not call directly.
- `daemon(callable $iteration, int $sleepSeconds = 10, int $restartAfterSeconds = 3600, bool $once = false): void` - run a worker loop for use in a `php artisan` command. `$iteration($wokeUp)` should return truthy when it did work; falsy triggers sleep. Respects maintenance mode. Rethrows as `InternalValidationException` if a `ValidationException` escapes.
- `exchangerPush(string $key, mixed $item, int $expiredSeconds = 86400): void` - Redis-backed list push with TTL (Lua script, atomic).
- `exchangerPull(string $key): array` - atomic LRANGE+DEL returning unserialized items.
- `normalizeKey(mixed $value, bool $encodeToJson = true, bool $nullTolerance = true): mixed` - normalize a value for hashing (trim+lowercase strings, cast scalars to string, etc.). Throws on `null` when `$nullTolerance=false` and not in production.

### `AnourValar\LaravelAtom\Registry`

In-memory store for pending transaction callbacks, keyed by `[event][connectionName]`. Constructor takes no args. Public methods: `push`, `remove`, `pull`. Normally you only touch this via `Atom`.

### `AnourValar\LaravelAtom\Mapper` (abstract)

Base class for strict DTOs. Implements `JsonSerializable`, `ArrayAccess`, `Illuminate\Contracts\Database\Eloquent\Castable` (so a Mapper subclass can be used directly in `$casts`).

- You MUST declare a constructor with Constructor Property Promotion. Throws otherwise.
- `Mapper::from(array|object $data): static` - hydrate from array or anything with `->toArray()`. Recursively hydrates nested `Mapper`s and parses `Carbon` properties via `Date::parse()`.
- `toArray(): array` - dump back to array (omits `Optional` values; applies `#[Jsonb]` sorting if set on the class).
- `only(array|string $keys): array` - filter `toArray()` to certain keys.
- `exists(): bool` - returns `true` (override or compare to `Optional` which returns `false`).
- Attribute classes under `AnourValar\LaravelAtom\Mapper\`:
  - `#[Mapping('external_name')]` - rename a property in array form.
  - `#[MappingSnakeCase]` - on class: all properties become snake_case; on property: just that one.
  - `#[Cast('int'|'string'|...)]` - settype after hydration.
  - `#[Mutate([Class::class, 'method'])]` or any callable - transform value after hydration.
  - `#[DefaultValue($value)]` - fallback when key missing.
  - `#[Exclude]` - hide property from `toArray()` output.
  - `#[ArrayOf(InnerMapper::class)]` - hydrate each element of an array property as `InnerMapper`.
  - `#[Jsonb]` (class-level) - sort keys deterministically (length, then alpha) on `toArray()`, suitable for stable JSONB columns.
  - `Optional` - special placeholder type; union it with the real type (e.g. `array|Optional $b`) to make the property truly optional. Absent input becomes an `Optional` instance, which `toArray()` omits.

### `AnourValar\LaravelAtom\MapperCollection` (abstract)

Typed collection of one `Mapper` class. Implements `Iterator`, `JsonSerializable`, `ArrayAccess`, `Countable`, `Castable`.

- Subclass MUST implement `protected function mapper(): string` returning a `Mapper` subclass FQCN.
- `MapperCollection::from(array|object $data): static`, `toArray()`, `count()`, plus full iterator/ArrayAccess support.
- Honors `#[Jsonb]` class attribute too.

### `AnourValar\LaravelAtom\MapperLazy`

Iterator/Countable wrapper around a deferred data source. Constructor takes a `callable` that returns the array on first access. Useful when the dataset is expensive and may not be iterated.

```php
$lazy = new \AnourValar\LaravelAtom\MapperLazy(fn () => SomeModel::all()->toArray());
foreach ($lazy as $item) { /* loaded on first rewind */ }
```

### `AnourValar\LaravelAtom\ArrayAccessMapper`

Lightweight read/write `ArrayAccess` wrapper around an array.

- `new ArrayAccessMapper(array $data, bool $instanceInsteadOfNull = false)` - the second flag makes unknown offsets return an empty `ArrayAccessMapper` instead of `null`, allowing safe chained access in Blade.
- `toArray(): array`, `has(...$keys): bool` (any), `hasAll(...$keys): bool` (all), array-access setters/getters.

### `AnourValar\LaravelAtom\Repository` (abstract)

Base class for raw-query repositories. Protected helpers:

- `getPlaceholders(array $attributes): string` - returns `?, ?, ?`.
- `getBindings(array $attributes): array` - `array_values`.
- `getRawValues(array|string $attributes): string` - comma-joined, each via `DB::escape()`.
- `mergeSources(array $sources, string $groupBy, array $structure = []): array` - merge several query results by a key, filling missing fields.
- `toArray($data): array` - deep object-to-array conversion.
- `gap(array $data, CarbonInterface $dateFrom, CarbonInterface $dateTo, array|callable $defaults, string $format = 'Y-m-d'): array` - fill missing dates in an aggregated dataset.
- `ungroup(array $data, array $groupBy = [], array $aggregations = ['qty']): array` - re-aggregate.
- `calculateSimplePaginate(int $perPage, int $page): array` - returns `['limit' => $perPage + 1, 'offset' => ...]`.
- `simplePaginate($data, int $perPage, int $page): \Illuminate\Pagination\Paginator`.
- `conditions(...$items): array` - SQL `AND ...` builder from `[bool => fn () => [$sql, $bindings]]` pairs.

### `AnourValar\LaravelAtom\AnalyticsInterface` + `AnourValar\LaravelAtom\Http\AnalyticsTrait`

Pattern for cached, validated, authorized analytics endpoints.

Interface methods:
- `validate(?User $user, Validator &$validator): void`
- `authorize(?User $user, array $data): void` (throws `AuthorizationException`)
- `cacheTag(array $data): ?string`
- `getData(array $data): iterable`

`AnalyticsTrait::retrieveAnalytics(array $request = [], int $cacheSeconds = 15*60, string $configPath = 'entities.analytics.type'): array` reads the `type` route param, resolves `config("{$configPath}.{$type}.bind")` to an `AnalyticsInterface` instance, validates+authorizes the request, and returns `compact('handler', 'request', 'response')` with the response cached via `Cache::tags(...)`.

### Helpers (`AnourValar\LaravelAtom\Helpers\*`)

Plain service classes - resolve via `app(Helper::class)` or DI. None of them are facades.

- `DateHelper`: `formatDate`, `formatDateTime`, `formatTime`, `formatDateRelative`, `dayShort/dayFull`, `monthShort/monthFull/monthFullCase` - all timezone-aware via `config('atom.timezone_client')` and locale via `laravel-atom::formats.*` translations.
- `NumberHelper`: `encodeMultiple` / `decodeMultiple` (integer-cent storage using `config('atom.number.multiple')`), `formatMultiple`, `formatNumber` (BC Math number_format), `spellout` (amount-as-words via `MessageFormatter`).
- `StringHelper`: `canonizePhone`, `formatPhone`, `canonizeEmail`, `encrypt`/`decrypt` (with custom base64 key), `encryptBinary`/`decryptBinary` (OPENSSL_RAW_DATA), `mask`, `nameFull`, `nameShort`, `normalizeUrl`.
- `ArrayHelper`: `jsonPretty`, `export` (PSR-style PHP array dump), `getStructureDiff`, `getTypeDiff`, `mergeRecursive`, `publishCollection` (Eloquent → filtered JSON with eager-load and append support), `applyDataToSchema` (replaces `%key%` / `$key$` markers).
- `LayoutHelper`: `getMenu($currentRoute, User $user, array|string $menu): array` - filters menu items by `user_ability` (Gate), `config_conditions`, evaluates `counter` closures, sets `is_active` / `url`.

### Strategies (`AnourValar\LaravelAtom\Strategies\*`)

All implement `StrategyInterface::lock(string $sha1, Connection $connection): void`.

- `PessimisticAdvisoryStrategy` (default) - PostgreSQL `pg_advisory_xact_lock`. Requires an open transaction.
- `OptimisticAdvisoryStrategy` - PostgreSQL `pg_try_advisory_xact_lock`; throws `OptimisticException` if already held.
- `PessimisticTransactionStrategy` - blocking row lock on the `locks` table (deprecated, needs the migration).
- `OptimisticTransactionStrategy` - `FOR UPDATE NOWAIT` on the `locks` table; throws `OptimisticException` on conflict (deprecated).

### Traits

- `AnourValar\LaravelAtom\Traits\PostgresTrait` (intended for migrations): `addGinIndex`, `removeDefault`, `syncAutoIncrement`, `installPgTrgm`, `conditionalIndexNotNull`, `addRumFullText`.
- `AnourValar\LaravelAtom\Traits\OptimizeCheckerTrait` (intended for PHPUnit test cases extending Orchestra Testbench): disables seqscan, asserts every `select ... where ...` query plan is index-backed.
- `AnourValar\LaravelAtom\Traits\EloquentCast` - used internally by `Mapper`/`MapperCollection` to satisfy `Castable`. You typically don't need to use it directly.

### Exceptions (`AnourValar\LaravelAtom\Exceptions\*`)

- `OptimisticException` - thrown by optimistic strategies when the lock is already held.
- `InternalValidationException` - wraps a `ValidationException` with `setContext()/context()` for logging in queue/daemon contexts. `fromValidationException()` static factory.
- `ExternalException(string $action, array|null|\AnourValar\HttpClient\Response $dump = null)` - for third-party-API failures; logs once via `report()`, exposes Horizon `context()`.
- `UnsupportedClientException` - renders a 400 JSON `{"message":"The client is no longer supported.","errors":[]}`.

### Middlewares

Register manually in your HTTP/Job kernel.

- `Http\Middleware\Config:configKey,expectedValue` and `Http\Middleware\ConfigNot:configKey,blockedValue` - gate routes on a config value.
- `Http\Middleware\IpWhitelist:configKeyForIps` - 401 unless `request()->ip()` matches `config($key)`.
- `Http\Middleware\JsonResponseFlags` - forces `JSON_UNESCAPED_UNICODE`.
- `Http\Middleware\Api\Token:configKeyForTokens` - 401 unless `Authorization` header matches one of the tokens.
- `Http\Middleware\Api\ClientRestrict:client1,client2,...` - 400 (`UnsupportedClientException`) if `X-Api-Restrict` is not whitelisted.
- `Http\Middleware\Api\Locale:en,ru,...` - sets `App::setLocale()` from `Accept-Language` (first 2 chars).
- `Http\Middleware\Web\Locale:en,ru,...` - sets `App::setLocale()` from `?locale=` input.
- `Http\Middleware\Api\Timezone` - sets `config(['atom.timezone_client' => ...])` from the `X-Timezone` header.
- `Http\Middleware\Api\Json` - forces `Accept: application/json`.
- `Http\Middleware\Api\HandlePrecognitiveRequests` - precognition support that rebinds the controller dispatchers.
- `Http\Middleware\Web\Select2` - input/output adapter for Select2 AJAX (`?term=`, length 3..100).
- `Jobs\Middleware\ValidationExceptionHandler` - converts `ValidationException` thrown inside jobs into `InternalValidationException` and fails (no retry).

## Usage examples

### Transaction-aware side-effects

```php
use AnourValar\LaravelAtom\Facades\AtomFacade as Atom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

DB::transaction(function () use ($order) {
    $order->save();

    // Schedule a job AFTER the outermost transaction commits
    Atom::onCommit(fn () => dispatch(new \App\Jobs\SendOrderEmail($order)));

    // Clean up an uploaded draft if anything rolls back
    Atom::onRollBack(fn () => Storage::delete($order->draft_path));

    // De-duplicate by unique name (later call replaces earlier)
    Atom::onCommit(fn () => Cache::forget("order:{$order->id}"), null, "forget:{$order->id}");
});
```

### Named locks (race-condition protection)

```php
use AnourValar\LaravelAtom\Facades\AtomFacade as Atom;
use AnourValar\LaravelAtom\Exceptions\OptimisticException;
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($user) {
    Atom::lockUser($user->id);  // = Atom::lock('user', $user->id)
    // critical section
});

// Optimistic: fail fast instead of waiting
try {
    DB::transaction(function () {
        Atom::strategy('optimistic_advisory')->lock('weekly_report');
        // generate report once at a time
    });
} catch (OptimisticException $e) {
    // already running elsewhere - just skip
}
```

### Mapper as Eloquent JSON cast

```php
use AnourValar\LaravelAtom\Mapper;
use AnourValar\LaravelAtom\Mapper\Mapping;
use AnourValar\LaravelAtom\Mapper\Cast;
use AnourValar\LaravelAtom\Mapper\ArrayOf;
use AnourValar\LaravelAtom\Mapper\Jsonb;

#[Jsonb] // stable key ordering for diff-friendly JSONB columns
class ShippingAddress extends Mapper
{
    public function __construct(
        public string $city,
        #[Mapping('zip')]            // stored as "zip" in JSON
        public string $postalCode,
        #[Cast('int')]
        public int $floor = 1,
    ) {}
}

// In the model:
class Order extends \Illuminate\Database\Eloquent\Model
{
    protected $casts = [
        'shipping_address' => ShippingAddress::class,
    ];
}

$order->shipping_address = ['city' => 'Berlin', 'zip' => '10115', 'floor' => '3'];
$order->save();
$order->shipping_address->city;     // "Berlin"
$order->shipping_address->floor;    // int(3)
```

### MapperCollection cast

```php
use AnourValar\LaravelAtom\MapperCollection;

class LineItems extends MapperCollection
{
    protected function mapper(): string { return LineItem::class; }
}

class Invoice extends \Illuminate\Database\Eloquent\Model
{
    protected $casts = ['lines' => LineItems::class];
}
```

### Daemon command

```php
use AnourValar\LaravelAtom\Facades\AtomFacade as Atom;

public function handle(): void
{
    Atom::daemon(function (?bool $wokeUp) {
        $job = \App\Models\PendingJob::query()->lockForUpdate()->first();
        if (! $job) {
            return false; // -> sleep
        }
        $job->process();
        return true;      // -> immediately iterate again
    }, sleepSeconds: 5, restartAfterSeconds: 1800);
}
```

## Conventions / gotchas

- All Mapper subclasses MUST use Constructor Property Promotion; calling `Mapper::from()` on a class with a manual constructor throws. The default `__construct` on the base class also throws to enforce this.
- `Mapper::from()` throws `RuntimeException` on unused/unexpected input keys, but ONLY outside production (`App::isProduction() === false`). Don't rely on the check in prod.
- Property names map 1-to-1 by default. Use `#[Mapping('snake_name')]` or `#[MappingSnakeCase]` (class- or property-level) to bridge snake_case JSON to camelCase PHP. `Optional` makes a property truly optional; type it as `MyType|Optional`.
- `Atom::lock()` REQUIRES being inside an open DB transaction when using any of the bundled strategies (they throw `LogicException` otherwise). Wrap calls in `DB::transaction(...)`.
- The default lock strategy `pessimistic_advisory` is PostgreSQL-only (uses `pg_advisory_xact_lock`). For MySQL/SQLite, switch to a `*_transaction` strategy and publish/run the `locks` table migration.
- `Atom::on*` callbacks fire on the outermost transaction only. They run on the actual `TransactionCommitted` / `TransactionRolledBack` Laravel events - if you fire callbacks inside nested transactions, they queue until the outer commit/rollback. Use `transactionZeroLevel()` in tests that wrap each case in a transaction.
- `Atom::onCommit/onRollBack` invoked OUTSIDE a transaction execute the callback immediately (commit case) or skip it silently (rollback case).
- The service provider auto-registers many `renderable()` exception handlers globally. If your application already customises these, expect conflicts and adjust accordingly.
- `config/bindings.php` is merged automatically; declare `'SomeInterface' => ['bind' => SomeImpl::class]` to get a singleton container binding (the package validates the binding implements the interface).
- `OptimizeCheckerTrait` is for testing only; it forces `SET enable_seqscan = 0` and asserts no `Seq Scan` appears in `EXPLAIN`, which only works on PostgreSQL.
- `Atom::exchangerPush/Pull` use `Illuminate\Support\Facades\Redis::eval` with Lua - your default Redis connection must support EVAL (i.e. not Predis cluster without scripts).
