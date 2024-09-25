<?php

namespace AnourValar\LaravelAtom\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait PostgresTrait
{
    /**
     * Create a GIN index
     *
     * @param string $tableName
     * @param string $column
     * @param string $option
     * @return void
     */
    protected function addGinIndex(string $tableName, string $column, string $option = 'gin_trgm_ops'): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($tableName, $column, $option) {
            if (Schema::getConnection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'pgsql') {
                Schema::getConnection()->statement(
                    "CREATE INDEX {$tableName}_{$column}_index ON {$tableName} USING gin ({$column} {$option});"
                );
            } else {
                $table->index($column);
            }
        });
    }

    /**
     * Remove default value from the column
     *
     * @param string $tableName
     * @param string $column
     * @return void
     */
    protected function removeDefault(string $tableName, string $column): void
    {
        \DB::statement("ALTER TABLE {$tableName} ALTER COLUMN {$column} DROP DEFAULT;");
    }

    /**
     * Sync the auto-increment state
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    protected function syncAutoIncrement(\Illuminate\Database\Eloquent\Model $model): void
    {
        if ($model->getConnection() instanceof \Illuminate\Database\PostgresConnection) {
            $table = $model->getTable();
            $key = $model->getKeyName();

            \DB::connection($model->getConnectionName())
                ->select("SELECT setval('{$table}_id_seq', max({$key})) FROM {$table}");
        }
    }

    /**
     * Enable pg_trgm extension
     *
     * @return void
     */
    protected function installPgTrgm(): void
    {
        if (\DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'pgsql') {
            \DB::statement('create extension if not exists pg_trgm;');
        }
    }

    /**
     * Create a conditional (not null) b-tree index
     *
     * @param string $tableName
     * @param string $column
     */
    protected function conditionalIndexNotNull(string $tableName, string $column)
    {
        \DB::statement(<<< HERE
            CREATE INDEX {$tableName}_{$column}_index ON {$tableName}
            USING btree ({$column}) WHERE ({$column} IS NOT NULL);
        HERE);
    }

    /**
     * create a fulltext search (tsvector) column with RUM index
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $locale
     * @return void
     */
    protected function addRumFullText(string $tableName, string $columnName = 'search_fulltext', string $locale = null): void
    {
        if (! $locale) {
            $locale = config('app.fulltext_locale', config('app.fallback_locale'));
        }

        \DB::statement("ALTER TABLE {$tableName} ADD COLUMN {$columnName} TSVECTOR");

        \DB::statement(<<<HERE
            CREATE INDEX {$tableName}_{$columnName}_rum ON {$tableName} USING rum ({$columnName} rum_tsvector_ops);
        HERE);
    }
}
