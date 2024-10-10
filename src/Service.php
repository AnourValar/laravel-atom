<?php

namespace AnourValar\LaravelAtom;

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
     * @param array $config
     * @param callable $lockHook
     * @return void
     */
    public function __construct(Registry $registry, array $config = null, callable $lockHook = null)
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
        $sha1 = sha1(serialize($this->canonizeArgs(func_get_args())));
        $connection = \DB::connection($this->config['locks']['connection']);

        $class = $this->config['locks']['strategies'][$this->config['locks']['strategy']];
        (new $class())->lock($sha1, $connection);

        if ($this->lockHook) {
            ($this->lockHook)($sha1, func_get_args());
        }
    }

    /**
     * Action after transaction commit
     *
     * @param callable $closure
     * @param string $connection
     * @return int|null
     */
    public function onCommit(callable $closure, string $connection = null): ?int
    {
        if (is_null($connection)) {
            $connection = \DB::getDefaultConnection();
        }

        if ($this->shouldCommit($connection)) {
            $closure();
            return null;
        }

        return $this->registry->push('commit', $connection, $closure);
    }

    /**
     * Action after transaction rollBack
     *
     * @param callable $closure
     * @param string $connection
     * @return int|null
     */
    public function onRollBack(callable $closure, string $connection = null): ?int
    {
        if (is_null($connection)) {
            $connection = \DB::getDefaultConnection();
        }

        if ($this->shouldRollBack($connection)) {
            return null;
        }

        return $this->registry->push('rollback', $connection, $closure);
    }

    /**
     * Remove "onCommit" task
     *
     * @param int $key
     * @param string $connection
     * @return void
     */
    public function removeOnCommit(int $key, string $connection = null): void
    {
        if (is_null($connection)) {
            $connection = \DB::getDefaultConnection();
        }

        $this->registry->remove('commit', $connection, $key);
    }

    /**
     * Remove "onRollBack" task
     *
     * @param int $key
     * @param string $connection
     * @return void
     */
    public function removeOnRollBack(int $key, string $connection = null): void
    {
        if (is_null($connection)) {
            $connection = \DB::getDefaultConnection();
        }

        $this->registry->remove('rollback', $connection, $key);
    }

    /**
     * @param mixed $event
     * @return void
     */
    public function triggerTransaction($event)
    {
        $connection = $event->connectionName;

        if ($event instanceof TransactionCommitted) {
            if (! $this->shouldCommit($connection)) {
                return;
            }

            foreach ($this->registry->pull('commit', $connection) as $task) {
                $task();
            }
            $this->registry->pull('rollback', $connection);
        }

        if ($event instanceof TransactionRolledBack) {
            if (! $this->shouldRollBack($connection)) {
                return;
            }

            foreach ($this->registry->pull('rollback', $connection) as $task) {
                $task();
            }
            $this->registry->pull('commit', $connection);
        }
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

        if (is_integer($value) || is_float($value)) {
            $value = (string) $value;
        }

        return $value;
    }

    /**
     * @param mixed $connection
     * @return bool
     */
    protected function shouldCommit($connection)
    {
        return (! (\DB::connection($connection)->transactionLevel() - $this->transactionZeroLevel));
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
