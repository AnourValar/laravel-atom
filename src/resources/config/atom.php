<?php

return [
    'locks' => [
        'connection' => null,
        'strategy' => 'pessimistic_transaction',

        'table' => 'locks',

        'strategies' => [
            'pessimistic_transaction' => AnourValar\LaravelAtom\Strategies\PessimisticTransactionStrategy::class,
            'optimistic_transaction' => AnourValar\LaravelAtom\Strategies\OptimisticTransactionStrategy::class,
        ],
    ],

    'number' => [
        'multiple' => 100,
    ],
];
