<?php

use PHPUnit\Framework\TestCase;

class NoopParseRequestTest extends TestCase {
	
	function testNoopParseRequest() {
		$values = array(
			'/path/to/noop/'=>'/',
			'/path/to/noop/folder'=>'/folder',
			'/path/to/noop/folder/'=>'/folder',
			'/path/to/noop/folder1/folder2'=>'/folder1/folder2',
			'/path/to/noop/folder1/folder2/'=>'/folder1/folder2',
			'/path/to/noop/folder_é'=>'/folder_é',
		);

		$dir = '/path/to/noop';
		
		foreach( $values as $k=>$v ) {
			noop::_parseRequest( $k, $dir );
			$this->assertEquals( $v, noop::get( 'request/url' ) );
		}
	}
	
	function testNoopParseRequestWithQuerystring() {
		$dir = '/path/to/noop';
		
		noop::_parseRequest( '/path/to/noop/?', $dir );
		$this->assertEquals( '/', noop::get( 'request/url' ) );
		$this->assertEquals( '', noop::get( 'request/qs' ) );
		
		noop::_parseRequest( '/path/to/noop/?qs', $dir );
		$this->assertEquals( '/', noop::get( 'request/url' ) );
		$this->assertEquals( 'qs', noop::get( 'request/qs' ) );
		
		noop::_parseRequest( '/path/to/noop/folder?', $dir );
		$this->assertEquals( '/folder', noop::get( 'request/url' ) );
		$this->assertEquals( '', noop::get( 'request/qs' ) );
		
		noop::_parseRequest( '/path/to/noop/folder/?qs', $dir );
		$this->assertEquals( '/folder', noop::get( 'request/url' ) );
		$this->assertEquals( 'qs', noop::get( 'request/qs' ) );
	}
	
}
