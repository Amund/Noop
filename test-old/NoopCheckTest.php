<?php

use PHPUnit\Framework\TestCase;

class NoopCheckTest extends TestCase {
	
	function testCheck01() {
		$reg = '#^\w+$#';
		$var = 'test';
		$this->assertEquals( TRUE, noop::check( $reg, $var ) );
	}
	
	function testCheck02() {
		$reg = '#^\d+$#';
		$var = 'error';
		$this->assertEquals( FALSE, noop::check( $reg, $var ) );
	}
	
	function testCheck03() {
		$reg = array(
			'key1'=>'#^\w{4}$#',
			'key2'=>'#^\d+$#',
		);
		$var = array(
			'key1'=>'test',
			'key2'=>'1234',
		);
		$errors = noop::check( $reg, $var );
		$this->assertEquals( array(), noop::check( $reg, $var ) );
	}
	
	function testCheck04() {
		$reg = array(
			'key1'=>'#^\w{4}$#',
			'key2'=>'#^\d+$#',
		);
		$var = array(
			'key1'=>'test',
			'key2'=>'error',
		);
		$errors = noop::check( $reg, $var );
		$this->assertEquals( array( 'key2'=>FALSE ), noop::check( $reg, $var ) );
	}
	
}
