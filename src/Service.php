<?php

namespace AnourValar\LaravelAtom;

use Illuminate\Database\Events\TransactionCommitting;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

class Service
{
    /**
     * @var \AnourValar\LaravelAtom\Registry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var callable
     */
    protected $lockHook;

    /**
     * @var int
     */
    protected $transactionZeroLevel = 0;

    /**
     * Setters
     *
     * @param \AnourValar\LaravelAtom\Registry $registry
     * @param array|null $config
     * @param callable|null $lockHook
     * @return void
     */
    public function __construct(Registry $registry, ?array $config = null, ?callable $lockHook = null)
    {
        $this->registry = $registry;

        if (is_null($config)) {
            $config = config('atom');
        }
        $this->config = $config;

        $this->lockHook = $lockHook;
    }

    /**
     * Set "zero level" for transactions
     *
     * @param int $level
     * @return void
     */
    public function transactionZeroLevel(int $level): void
    {
        $this->transactionZeroLevel = $level;
    }

    /**
     * Get instance with custom strategy
     *
     * @param string $strategy
     * @return self
     */
    public function strategy(string $strategy): self
    {
        return new self(
            new Registry(),
            array_replace_recursive($this->config, ['locks' => ['strategy' => $strategy]]),
            $this->lockHook
        );
    }

    /**
     * Magic calls
     *
     * @param string $method
     * @param array $args
     */
    public function __call($method, array $args)
    {
        if (mb_substr($method, 0, 4) == 'lock') {
            $entity = \Illuminate\Support\Str::snake(mb_substr($method, 4));

            return $this->lock($entity, ...$args);
        }

        trigger_error('Call to undefined method '.__CLASS__.'::'.$method, E_USER_ERROR);
    }

    /**
     * Apply lock
     *
     * @param ...$args
     * @return void
     */
    public function lock(): void
    {
        $key = serialize($this->canonizeArgs(func_get_args()));
        $sha1 = sha1($key);
        $connection = \DB::connection($this->config['locks']['connection']);

        $microtime = microtime(true);
        $class = $this->config['locks']['strategies'][$this->config['locks']['strategy']];
        (new $class())->lock($sha1, $connection);
        $microtime = microtime(true) - $microtime;

        if (
            $this->config['locks']['warning_wait_seconds']
            && $microtime > $this->config['locks']['warning_wait_seconds']
            && random_int(1, $this->config['locks']['warning_lottery'][1]) <= $this->config['locks']['warning_lottery'][0]
        ) {
            \Log::warning(
                "Locking took over {$this->config['locks']['warning_wait_seconds']} second(s)",
                ['key' => \Str::limit($key, 100), 'seconds' => round($microtime, 2)]
            );
        }

        if ($this->lockHook) {
            ($this->lockHook)($sha1, func_get_args());
        }
    }

    /**
     * Action before transaction commit
     *
     * @param callable $closure
     * @param string|null $connection
     * @param string|null $uniqueName
     * @return int|string|null
     */
    public function beforeCommit(callable $closure, ?string $connection = null, ?string $uniqueName = null): int|string|null
    {
        if (is_null($connection)) {
            $connection = \DB::getDefaultConnection();
        }

        if ($this->shouldCommit($connection)) {
            $closure();
            return null;
        }

        return $this->registry->push('before_commit', $connection, $closure, $uniqueName);
    }

    /**
     * Action after transaction commit
     *
     * @param callable $closure
     * @param string|null $connection
     * @param string|null $uniqueName
     * @return int|string|null
     */
    public function onCommit(callable $closure, ?string $connection = null, ?string $uniqueName = null): int|string|null
    {
        if (is_null($connection)) {
            $connection = \DB::getDefaultConnection();
        }

        if ($this->shouldCommit($connection)) {
            $closure();
            return null;
        }

        return $this->registry->push('commit', $connection, $closure, $uniqueName);
    }

    /**
     * Action after transaction rollBack
     *
     * @param callable $closure
     * @param string|null $connection
     * @param string|null $uniqueName
     * @return int|string|null
     */
    public function onRollBack(callable $closure, ?string $connection = null, ?string $uniqueName = null): int|string|null
    {
        if (is_null($connection)) {
            $connection = \DB::getDefaultConnection();
        }

        if ($this->shouldRollBack($connection)) {
            return null;
        }

        return $this->registry->push('rollback', $connection, $closure, $uniqueName);
    }

    /**
     * Remove event task
     *
     * @param string $event
     * @param int|string $key
     * @param string|null $connection
     * @return void
     */
    public function removeEvent(string $event, int|string $key, ?string $connection = null): void
    {
        if (is_null($connection)) {
            $connection = \DB::getDefaultConnection();
        }

        $this->registry->remove($event, $connection, $key);
    }

    /**
     * @param mixed $event
     * @return void
     */
    public function triggerTransaction($event)
    {
        $connection = $event->connectionName;

        if ($event instanceof TransactionCommitting) {
            if (! $this->shouldCommit($connection, 1)) {
                return;
            }

            foreach ($this->registry->pull('before_commit', $connection) as $task) {
                $task();
            }
        }

        if ($event instanceof TransactionCommitted) {
            if (! $this->shouldCommit($connection)) {
                return;
            }

            $this->registry->pull('rollback', $connection);
            foreach ($this->registry->pull('commit', $connection) as $task) {
                $task();
            }
        }

        if ($event instanceof TransactionRolledBack) {
            if (! $this->shouldRollBack($connection)) {
                return;
            }

            $this->registry->pull('commit', $connection);
            foreach ($this->registry->pull('rollback', $connection) as $task) {
                $task();
            }
        }
    }

    /**
     * Background process (for running in a command)
     *
     * @param callable $iteration
     * @param int $sleepSeconds
     * @param int $restartAfterSeconds
     * @param bool $once
     * @return void
     * @throws \AnourValar\LaravelAtom\Exceptions\InternalValidationException
     */
    public function daemon(callable $iteration, int $sleepSeconds = 10, int $restartAfterSeconds = 3600, bool $once = false): void
    {
        try {
            $time = now()->timestamp;
            $wokeUp = null;

            while (now()->timestamp - $time < $restartAfterSeconds && ! app()->maintenanceMode()->active()) {
                if (! $iteration($wokeUp)) {
                    if ($once) {
                        return;
                    }

                    $this->sleep($sleepSeconds);
                    $wokeUp = true;
                } else {
                    $wokeUp = false;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (! $once) {
                $this->sleep($sleepSeconds);
            }
            throw \AnourValar\LaravelAtom\Exceptions\InternalValidationException::fromValidationException($e);
        } catch (\Throwable $e) {
            if (! $once) {
                $this->sleep($sleepSeconds);
            }
            throw $e;
        }
    }

    /**
     * @param int $seconds
     * @return void
     */
    protected function sleep(int $seconds): void
    {
        // \Illuminate\Support\Sleep::fake();
        // \Illuminate\Support\Sleep::assertSequence([\Illuminate\Support\Sleep::for(5)->seconds()]);
        do {
            $seconds -= 5;

            if ($seconds >= 0) {
                \Illuminate\Support\Sleep::for(5)->seconds();
            } else {
                \Illuminate\Support\Sleep::for(5 + $seconds)->seconds();
            }
        } while ($seconds > 0 && ! app()->maintenanceMode()->active());
    }

    /**
     * @param mixed $value
     * @return mixed
     * @throws \RuntimeException
     */
    protected function canonizeArgs($value)
    {
        if ($value === null && ! \App::isProduction()) {
            throw new \RuntimeException('Null lock.');
        }

        if (is_array($value)) {
            foreach ($value as &$item) {
                $item = $this->canonizeArgs($item);
            }
            unset($item);
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if (is_integer($value) || is_double($value)) {
            $value = (string) $value;
        }

        if ($value === true) {
            $value = '1';
        }

        if ($value === false) {
            $value = '0';
        }

        return $value;
    }

    /**
     * @param mixed $connection
     * @param int $sub
     * @return bool
     */
    protected function shouldCommit($connection, int $sub = 0)
    {
        return (! (\DB::connection($connection)->transactionLevel() - $this->transactionZeroLevel - $sub));
    }

    /**
     * @param mixed $connection
     * @return bool
     */
    protected function shouldRollBack($connection)
    {
        return (! (\DB::connection($connection)->transactionLevel() - $this->transactionZeroLevel));
    }
}
