<?php

use PHPUnit\Framework\TestCase;

class NoopVarTest extends TestCase
{

	function testGet()
	{
		$this->assertEquals([], noop::get('', []));
		$this->assertEquals(['key' => 'value'], noop::get('', ['key' => 'value']));
		$this->assertEquals(['key' => 'value'], noop::get('/', ['key' => 'value']));
		$this->assertEquals('value', noop::get('key/subkey', ['key' => ['subkey' => 'value']]));

		$this->assertNull(noop::get(123, []));
		$this->assertNull(noop::get('undefined', ['key' => 'value']));
		$this->assertNull(noop::get('un/de/fi/ned', ['key' => 'value']));
		$this->assertNull(noop::get('key/undefined', ['key' => 'value']));
	}

	function testSet()
	{
		$arr = [];
		noop::set('key', 'value', $arr);
		$this->assertEquals('value', $arr['key']);

		$arr = [];
		noop::set('path/to/key', 'value', $arr);
		$this->assertEquals('value', $arr['path']['to']['key']);

		$arr = [];
		noop::set('', 'value', $arr);
		$this->assertEquals('value', $arr['']);
	}

	function testDel()
	{
		$arr = [];
		$out = noop::del('', $arr);
		$this->assertEquals($out, FALSE);

		$arr = [];
		$out = noop::del('unknown/key', $arr);
		$this->assertEquals($out, NULL);

		$arr = ['key' => 'value'];
		$out = noop::del('key', $arr);
		$this->assertEquals($out, 'value');
		$this->assertArrayNotHasKey('key', $arr);

		$arr = [];
		$arr['path']['to']['key'] = 'value';
		$out = noop::del('path/to/key', $arr);
		$this->assertEquals($out, 'value');
		$this->assertArrayNotHasKey('key', $arr['path']['to']);
	}

	function testGetWithInternalRegistry()
	{
		$this->assertEquals('index', noop::get('config/default/controller'));
		
		$this->assertTrue(noop::set('config/default/controller', 'foo'));
		$this->assertEquals('foo', noop::get('config/default/controller'));
		
		$this->assertEquals('foo',noop::del('config/default/controller'));
		$this->assertNull(noop::get('config/default/controller'));
	}
}
