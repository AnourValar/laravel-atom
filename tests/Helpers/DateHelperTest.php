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

    /**
     * @return void
     */
    public function test_formatDate()
    {
        \App::setLocale('ru'); // date_format: d.m.Y
        $helper = new \AnourValar\LaravelAtom\Helpers\DateHelper();

        $this->assertNull($helper->formatDate(null));
        $this->assertSame('n/a', $helper->formatDate(null, true, 'n/a'));

        // date-only string: no time component => no timezone shift
        $this->assertSame('26.03.2021', $helper->formatDate('2021-03-26'));

        // with time: shifts into the client timezone (Etc/GMT-3 == UTC+3)
        $this->assertSame('27.03.2021', $helper->formatDate('2021-03-26 22:00:00', true, null, 'Etc/GMT-3'));

        // time disabled: no timezone shift even though time is present
        $this->assertSame('26.03.2021', $helper->formatDate('2021-03-26 22:00:00', false, null, 'Etc/GMT-3'));

        // CarbonInterface input
        $this->assertSame('27.03.2021', $helper->formatDate(\Date::parse('2021-03-26 22:00:00'), true, null, 'Etc/GMT-3'));
    }

    /**
     * @return void
     */
    public function test_formatDateTime()
    {
        \App::setLocale('ru'); // datetime_format: d.m.Y H:i
        $helper = new \AnourValar\LaravelAtom\Helpers\DateHelper();

        $this->assertNull($helper->formatDateTime(null));
        $this->assertSame('n/a', $helper->formatDateTime(null, 'n/a'));

        $this->assertSame('27.03.2021 01:00', $helper->formatDateTime('2021-03-26 22:00:00', null, 'Etc/GMT-3'));
        $this->assertSame('27.03.2021 01:00', $helper->formatDateTime(\Date::parse('2021-03-26 22:00:00'), null, 'Etc/GMT-3'));
    }

    /**
     * @return void
     */
    public function test_formatTime()
    {
        $helper = new \AnourValar\LaravelAtom\Helpers\DateHelper();

        $this->assertNull($helper->formatTime(null));
        $this->assertSame('n/a', $helper->formatTime(null, 'n/a'));

        $this->assertSame('01:00', $helper->formatTime('2021-03-26 22:00:00', null, 'Etc/GMT-3'));
        $this->assertSame('22:00', $helper->formatTime('2021-03-26 22:00:00', null, 'UTC'));
    }

    /**
     * @return void
     */
    public function test_dayShort_dayFull()
    {
        \App::setLocale('en');
        $helper = new \AnourValar\LaravelAtom\Helpers\DateHelper();
        $date = \Date::parse('2021-03-26'); // Friday

        $this->assertNull($helper->dayShort(null));
        $this->assertNull($helper->dayFull(null));

        $this->assertSame('Fri', $helper->dayShort($date));
        $this->assertSame('Fri', $helper->dayShort($date, true));
        $this->assertSame('Friday', $helper->dayFull($date));
        $this->assertSame('Friday', $helper->dayFull($date, true));
    }

    /**
     * @return void
     */
    public function test_monthShort_monthFull()
    {
        \App::setLocale('en');
        $helper = new \AnourValar\LaravelAtom\Helpers\DateHelper();
        $date = \Date::parse('2021-03-26');

        $this->assertNull($helper->monthShort(null));
        $this->assertNull($helper->monthFull(null));

        $this->assertSame('Mar', $helper->monthShort($date));
        $this->assertSame('March', $helper->monthFull($date));
    }

    /**
     * @return void
     */
    public function test_monthFullCase()
    {
        $helper = new \AnourValar\LaravelAtom\Helpers\DateHelper();
        $date = \Date::parse('2021-03-26');

        $this->assertNull($helper->monthFullCase(null));

        \App::setLocale('en');
        $this->assertSame('March', $helper->monthFullCase($date));

        // genitive case + ucFirst branch
        \App::setLocale('ru');
        $this->assertSame('марта', $helper->monthFullCase($date));
        $this->assertSame('Марта', $helper->monthFullCase($date, true));
    }
}
