<?php

use PHPUnit\Framework\TestCase;

class NoopFilterTest extends TestCase {
	
	function testFilter01() {
		$src = array(
			'a'=>'1',
			'b'=>'2',
			'c'=>'3',
		);
		$allowed = array( 'a','c' );
		$expected = array(
			'a'=>'1',
			'c'=>'3',
		);
		$this->assertEquals( noop::filter( $src, $allowed ), $expected );
	}
	
}
