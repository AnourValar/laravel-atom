<?php

namespace AnourValar\LaravelAtom;

use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

class Service
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Setters
     *
     * @param array $config
     * @return  void
     */
    public function __construct(array $config = null)
    {
        if (is_null($config)) {
            $config = config('atom');
        }

        $this->config = $config;
    }

    /**
     * Get instance with custom strategy
     *
     * @param string $strategy
     * @return self
     */
    public function strategy(string $strategy) : self
    {
        return new self(array_replace_recursive($this->config, ['locks' => ['strategy' => $strategy]]));
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
    public function lock() : void
    {
        $sha1 = sha1('/' . $this->canonizeArgs(func_get_args()) . '/');
        $connection = \DB::connection($this->config['locks']['connection']);
        $table = $this->config['locks']['table'];

        $class = $this->config['locks']['strategies'][$this->config['locks']['strategy']];
        (new $class)->lock($sha1, $connection, $table);

        $this->cleanUp($connection, $table);
    }

    /**
     * Action after transaction commit
     *
     * @param callable $closure
     * @param string $connection
     * @return void
     */
    public function onCommit(callable $closure, string $connection = null) : void
    {
        static $booted;

        if (is_null($connection)) {
            $connection = \DB::getDefaultConnection();
        }

        if ($this->shouldCommit($connection)) {
            $closure();
            return;
        }

        Registry::push('commit', $connection, $closure);

        if ($booted) {
            return;
        }
        $booted = true;

        \Event::listen([TransactionCommitted::class, TransactionRolledBack::class], function ($event)
        {
            $connection = $event->connectionName;

            if (! $this->shouldCommit($connection)) {
                return;
            }

            $list = Registry::pull('commit', $connection);
            if ($event instanceof TransactionCommitted) {
                foreach ($list as $task) {
                    $task();
                }
            }
        });
    }

    /**
     * Action after transaction rollBack
     *
     * @param callable $closure
     * @param string $connection
     * @return void
     */
    public function onRollBack(callable $closure, string $connection = null) : void
    {
        static $booted;

        if (is_null($connection)) {
            $connection = \DB::getDefaultConnection();
        }

        if ($this->shouldRollBack($connection)) {
            return;
        }

        Registry::push('rollBack', $connection, $closure);

        if ($booted) {
            return;
        }
        $booted = true;

        \Event::listen([TransactionCommitted::class, TransactionRolledBack::class], function ($event)
        {
            $connection = $event->connectionName;

            if (! $this->shouldRollBack($connection)) {
                return;
            }

            $list = Registry::pull('rollBack', $connection);
            if ($event instanceof TransactionRolledBack) {
                foreach ($list as $task) {
                    $task();
                }
            }
        });
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
    protected function cleanUp(\Illuminate\Database\Connection $connection, string $table) : void
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
     * @return boolean
     */
    protected function shouldCommit($connection)
    {
         return (! \DB::connection($connection)->transactionLevel());
    }

    /**
     * @param mixed $connection
     * @return boolean
     */
    protected function shouldRollBack($connection)
    {
         return (! \DB::connection($connection)->transactionLevel());
    }
}
