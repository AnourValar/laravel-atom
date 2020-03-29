<?php

return [
    'locks' => [
        'connection' => null,
        'strategy' => 'pessimistic_transaction',

        'table' => 'locks',

        'strategies' => [
            'pessimistic_transaction' => AnourValar\LaravelAtom\Strategies\PessimisticTransactionStrategy::class,
        ],
    ],
];
