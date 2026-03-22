<?php

namespace AnourValar\LaravelAtom\Tests\Helpers;

class DateHelperTest extends \AnourValar\LaravelAtom\Tests\AbstractSuite
{
    /**
     * @return void
     */
    public function test_formatDateRelative()
    {
        \App::setLocale('ru');
        \Date::setTestNow('2021-03-26 12:00:00');
        $helper = new \AnourValar\LaravelAtom\Helpers\DateHelper();

        $this->assertEquals('01.03.2022', $helper->formatDateRelative(\Date::parse('2022-03-01 12:00')));
        $this->assertEquals('10 Декабря', $helper->formatDateRelative(\Date::parse('2021-12-10 12:00')));
        $this->assertEquals('29 Марта', $helper->formatDateRelative(\Date::parse('2021-03-29 12:00')));
        $this->assertEquals('Послезавтра', $helper->formatDateRelative(\Date::parse('2021-03-28 12:00')));
        $this->assertEquals('Завтра', $helper->formatDateRelative(\Date::parse('2021-03-27 12:00')));
        $this->assertEquals('Сегодня', $helper->formatDateRelative(\Date::parse('2021-03-26 12:00')));
        $this->assertEquals('Вчера', $helper->formatDateRelative(\Date::parse('2021-03-25 12:00')));
        $this->assertEquals('Позавчера', $helper->formatDateRelative(\Date::parse('2021-03-24 12:00')));
        $this->assertEquals('23 Марта', $helper->formatDateRelative(\Date::parse('2021-03-23 12:00')));
        $this->assertEquals('10 Января', $helper->formatDateRelative(\Date::parse('2021-01-10 12:00')));
        $this->assertEquals('01.03.2020', $helper->formatDateRelative(\Date::parse('2020-03-01 12:00')));
    }
}
