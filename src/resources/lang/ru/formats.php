<?php

return [
    'datetime_format' => 'd.m.Y H:i',
    'date_format' => 'd.m.Y',

    'spellout' => ':spellout_int :spellout_dec',
    'spellout_int' => ':int рубль|:int рубля|:int рублей',
    'spellout_dec' => ':dec копейка|:dec копейки|:dec копеек',

    'human_date' => [
        'after_tomorrow' => 'Послезавтра',
        'tomorrow' => 'Завтра',
        'today' => 'Сегодня',
        'yesterday' => 'Вчера',
        'before_yesterday' => 'Позавчера',
        'current_year' => [
            '01' => ':day Января',
            '02' => ':day Февраля',
            '03' => ':day Марта',
            '04' => ':day Апреля',
            '05' => ':day Мая',
            '06' => ':day Июня',
            '07' => ':day Июля',
            '08' => ':day Августа',
            '09' => ':day Сентября',
            '10' => ':day Октября',
            '11' => ':day Ноября',
            '12' => ':day Декабря',
        ],
    ],
];
