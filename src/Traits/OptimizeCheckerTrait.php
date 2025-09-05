<?php

namespace AnourValar\LaravelAtom\Traits;

trait OptimizeCheckerTrait
{
    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        $this->registerChecker();

        \Queue::after(function ($job) {
            \App::make(\Illuminate\Contracts\Cache\Repository::class)->getStore()->locks = [];
        });

        return $uses;
    }

    /**
     * @return void
     */
    protected function registerChecker(): void
    {
        if (\DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'pgsql') {
            \DB::update('SET enable_seqscan = 0');
            //$this->assertEquals('off', \DB::select("SHOW enable_seqscan")[0]->enable_seqscan);
        }

        \DB::listen(function ($query) {
            $sql = $query->sql;

            if (stripos($sql, 'select ') !== 0) {
                return;
            }

            if (! stripos($sql, ' where ')) {
                return;
            }

            if (stripos($sql, ' "migrations" ')) {
                return;
            }

            $sql = preg_replace('|\s+limit\s+\d+\s*$|iu', '', $sql);

            foreach (\DB::select("EXPLAIN {$sql}", $query->bindings) as $item) {
                $item = (array) $item;
                $item = $item['QUERY PLAN'];

                if (mb_stripos($item, 'Seq Scan') !== false) {
                    $this->assertStringNotContainsString(
                        'Seq Scan',
                        $item,
                        $sql . ' ['.implode(', ', $query->bindings).']'
                    );
                }
            }

        });
    }
}
