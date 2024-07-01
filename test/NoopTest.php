<?php

use PHPUnit\Framework\TestCase;

/* TODO
inspect,check,p
*/

class NoopTest extends TestCase
{

	function testNoopLibraryMustBeLoaded()
	{
		$this->assertTrue(class_exists('noop'), 'Noop library not loaded');
	}

	function testNoopClassHaveStructure()
	{
		$methods = array(
			'start', 'view', 'pdo', // core methods
			'get', 'set', 'del', // manipulates private $var registry (and associative arrays)
			'output', 'redirect', 'status', // http response
			//'check', 'filter', // helpers
			'inspect', 'benchmark' // DEV tools
		);
		foreach ($methods as $method)
			$this->assertTrue(method_exists('noop', $method), 'No ' . $method . ' method');

		$this->assertIsArray( noop::$var );
	}
}
