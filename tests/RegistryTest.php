<?php

namespace AnourValar\LaravelAtom\Tests;

class RegistryTest extends AbstractSuite
{
    /**
     * @return void
     */
    public function test_push_returns_incremental_keys()
    {
        $registry = new \AnourValar\LaravelAtom\Registry();

        $this->assertSame(0, $registry->push('saved', 'mysql', fn () => 'a'));
        $this->assertSame(1, $registry->push('saved', 'mysql', fn () => 'b'));
        $this->assertSame('custom', $registry->push('saved', 'mysql', fn () => 'c', 'custom'));
    }

    /**
     * @return void
     */
    public function test_pull_returns_and_flushes_closures()
    {
        $registry = new \AnourValar\LaravelAtom\Registry();

        $registry->push('saved', 'mysql', fn () => 'a');
        $registry->push('saved', 'mysql', fn () => 'b');
        $registry->push('saved', 'mysql', fn () => 'c', 'custom');

        $tasks = $registry->pull('saved', 'mysql');
        $this->assertSame([0, 1, 'custom'], array_keys($tasks));
        $this->assertSame('a', $tasks[0]());
        $this->assertSame('b', $tasks[1]());
        $this->assertSame('c', $tasks['custom']());

        // a second pull is empty (the first one flushed the queue)
        $this->assertSame([], $registry->pull('saved', 'mysql'));
    }

    /**
     * @return void
     */
    public function test_event_and_connection_isolation()
    {
        $registry = new \AnourValar\LaravelAtom\Registry();

        $registry->push('saved', 'mysql', fn () => 'a');
        $registry->push('deleted', 'mysql', fn () => 'b');
        $registry->push('saved', 'pgsql', fn () => 'c');

        $this->assertCount(1, $registry->pull('saved', 'mysql'));

        // pulling one bucket leaves the others untouched
        $this->assertCount(1, $registry->pull('deleted', 'mysql'));
        $this->assertCount(1, $registry->pull('saved', 'pgsql'));

        // unknown bucket
        $this->assertSame([], $registry->pull('unknown', 'mysql'));
    }

    /**
     * @return void
     */
    public function test_remove()
    {
        $registry = new \AnourValar\LaravelAtom\Registry();

        $registry->push('saved', 'mysql', fn () => 'a');
        $registry->push('saved', 'mysql', fn () => 'b');

        $registry->remove('saved', 'mysql', 0);

        $tasks = $registry->pull('saved', 'mysql');
        $this->assertSame([1], array_keys($tasks));
        $this->assertSame('b', $tasks[1]());
    }

    /**
     * @return void
     */
    public function test_push_with_same_key_replaces_and_reorders()
    {
        $registry = new \AnourValar\LaravelAtom\Registry();

        $registry->push('saved', 'mysql', fn () => 'first', 'key');
        $registry->push('saved', 'mysql', fn () => 'other', 'other');
        $registry->push('saved', 'mysql', fn () => 'second', 'key'); // replaces and moves to the end

        $tasks = $registry->pull('saved', 'mysql');
        $this->assertSame(['other', 'key'], array_keys($tasks));
        $this->assertSame('second', $tasks['key']());
    }
}
