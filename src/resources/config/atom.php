<?php

return [
    'locks' => [
        'connection' => null,
        'strategy' => 'pessimistic_advisory',

        'strategies' => [
            'pessimistic_advisory' => AnourValar\LaravelAtom\Strategies\PessimisticAdvisoryStrategy::class,
            'optimistic_advisory' => AnourValar\LaravelAtom\Strategies\OptimisticAdvisoryStrategy::class,

            'pessimistic_transaction' => AnourValar\LaravelAtom\Strategies\PessimisticTransactionStrategy::class, // @deprecated
            'optimistic_transaction' => AnourValar\LaravelAtom\Strategies\OptimisticTransactionStrategy::class, // @deprecated
        ],

        'warning_wait_seconds' => 0.5,
        'warning_lottery' => [25, 100],
    ],

    'number' => [
        'multiple' => 100, // 10000000000
    ],
];
