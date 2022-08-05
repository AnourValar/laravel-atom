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
     * @var array
     */
    protected $booted = ['commit' => false, 'rollback' => false];

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
        $sha1 = sha1('/' . $this->canonizeArgs(func_get_args()) . '/');
        $connection = \DB::connection($this->config['locks']['connection']);
        $table = $this->config['locks']['table'];

        $class = $this->config['locks']['strategies'][$this->config['locks']['strategy']];
        (new $class)->lock($sha1, $connection, $table);

        if ($this->lockHook) {
            ($this->lockHook)($sha1, func_get_args());
        }
        $this->cleanUp($connection, $table);
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

        $key = $this->registry->push('commit', $connection, $closure);

        if ($this->booted['commit']) {
            return $key;
        }
        $this->booted['commit'] = true;

        \Event::listen([TransactionCommitted::class, TransactionRolledBack::class], function ($event) {
            $connection = $event->connectionName;

            if (! $this->shouldCommit($connection)) {
                return;
            }

            $list = $this->registry->pull('commit', $connection);
            if ($event instanceof TransactionCommitted) {
                foreach ($list as $task) {
                    $task();
                }
            }
        });

        return $key;
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

        $key = $this->registry->push('rollback', $connection, $closure);

        if ($this->booted['rollback']) {
            return $key;
        }
        $this->booted['rollback'] = true;

        \Event::listen([TransactionCommitted::class, TransactionRolledBack::class], function ($event) {
            $connection = $event->connectionName;

            if (! $this->shouldRollBack($connection)) {
                return;
            }

            $list = $this->registry->pull('rollback', $connection);
            if ($event instanceof TransactionRolledBack) {
                foreach ($list as $task) {
                    $task();
                }
            }
        });

        return $key;
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
     * @param mixed $value
     * @return mixed
     */
    protected function canonizeArgs($value)
    {
        if (is_scalar($value)) {
            if (is_string($value)) {
                $value = trim(mb_strtolower($value));
            }

            if ($value === '' || $value === false) {
                return 0;
            }

            return $value;
        }

        if (is_iterable($value)) {
            foreach ($value as &$item) {
                $item = $this->canonizeArgs($item);
            }
            unset($item);

            return implode('/', $value);
        }

        return 0;
    }

    /**
     * @param \Illuminate\Database\Connection $connection
     * @param string $table
     * @return  void
     */
    protected function cleanUp(\Illuminate\Database\Connection $connection, string $table): void
    {
        if (! mt_rand(0, 10)) {
            $connection
                ->table($table)
                ->where('updated_at', '<=', date('Y-m-d H:i:s', strtotime('-1 day')))
                ->delete();
        }
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
