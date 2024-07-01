<?php

use PHPUnit\Framework\TestCase;

class NoopEventTest extends TestCase {
    public function setUp(): void {
        noop::$var['events'] = [];
    }

	function testAddAnEvent() {
        noop::event('foo', 'description','bar');
		$this->assertArrayHasKey('foo', noop::$var['events']);
		$this->assertEquals(['description','bar'], noop::$var['events']['foo'][0]);
	}

	function testAddAnEventWithArguments() {
        noop::event('foo', 'description', 'bar', 'baz');
		$this->assertArrayHasKey('foo', noop::$var['events']);
		$this->assertEquals(['description', 'bar','baz'], noop::$var['events']['foo'][0]);
	}

	function testExecuteCallbacksForAnEvent() {
        noop::$var['var']['foo'] = 'bar';
        $myCallback = function($value) { noop::$var['var']['foo'] = $value; };

        noop::$var['events'] = [
            'foo' => [
                0 => ['description', $myCallback, 'baz'],
            ],
        ];

        noop::event('foo');

		$this->assertEquals('baz', noop::$var['var']['foo']);
	}
}
