<?php

use PHPUnit\Framework\TestCase;

class NoopBenchmarkTest extends TestCase
{
    public function setUp(): void
    {
        noop::$var['benchmark'] = [];
    }

    function testBenchmark()
    {
        $this->assertArrayNotHasKey('foo', noop::$var['benchmark']);

        // start benchmark
        noop::benchmark('foo', TRUE);

        // stop benchmark
        noop::benchmark('foo', FALSE);

        // check benchmark
        $this->assertArrayHasKey('foo', noop::$var['benchmark']);
        $this->assertGreaterThan(0, noop::$var['benchmark']['foo']['start']);
        $this->assertGreaterThan(0, noop::$var['benchmark']['foo']['stop']);
        $this->assertGreaterThanOrEqual(noop::$var['benchmark']['foo']['start'], noop::$var['benchmark']['foo']['stop']);

        // retrieve benchmark
        $value = noop::benchmark('foo');
        $this->assertGreaterThanOrEqual(0, $value);
    }
}
