<?php

use PHPUnit\Framework\TestCase;

class NoopParseRequestTest extends TestCase
{
	public function setUp(): void
	{
		$_SERVER = [
			'REQUEST_METHOD' => 'GET',
			'HTTP_HOST' => 'localhost',
			'SERVER_PORT' => '80',
			'SCRIPT_NAME' => '/index.php',
			'REQUEST_URI' => '/',
		];
	}

	public function testBasic(): void
	{
		$request = noop::parseRequest($_SERVER);

		$this->assertEquals([
			'method' => 'GET',
			'protocol' => 'http',
			'host' => 'localhost',
			'port' => '80',
			'uri' => '/',
			'basePath' => '',
			'baseUrl' => 'http://localhost',
			'path' => '/',
			'qs' => '',
			'parsedUrl' => 'http://localhost/',
			'url' => 'http://localhost/',
		], $request);
	}

	public function testMethod(): void
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$request = noop::parseRequest($_SERVER);

		$this->assertEquals('POST', $request['method']);
	}

	public function testHttps(): void
	{
		$_SERVER['HTTPS'] = 'on';
		$request = noop::parseRequest($_SERVER);

		$this->assertEquals('https', $request['protocol']);
	}

	public function testHttpsBehindProxy(): void
	{
		$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
		$request = noop::parseRequest($_SERVER);

		$this->assertEquals('https', $request['protocol']);
	}

	public function testShowPortInUrl(): void
	{
		$_SERVER['SERVER_PORT'] = '42';
		$request = noop::parseRequest($_SERVER);

		$this->assertEquals('42', $request['port']);
		$this->assertStringContainsString(':42', (string) $request['url']);
	}

	public function testHidePort80InUrl(): void
	{
		$_SERVER['SERVER_PORT'] = '80';
		$request = noop::parseRequest($_SERVER);

		$this->assertEquals('80', $request['port']);
		$this->assertStringNotContainsString(':80', (string) $request['url']);
	}

	public function testHidePort443InUrl(): void
	{
		$_SERVER['SERVER_PORT'] = '443';
		$request = noop::parseRequest($_SERVER);

		$this->assertEquals('443', $request['port']);
		$this->assertStringNotContainsString(':443', (string) $request['url']);
	}

	public function testFromSubFolder(): void
	{
		$_SERVER['REQUEST_URI'] = '/subfolder/';
		$_SERVER['SCRIPT_NAME'] = '/subfolder/index.php';
		$request = noop::parseRequest($_SERVER);

		$this->assertEquals('/subfolder', $request['basePath']);
		$this->assertEquals('http://localhost/subfolder', $request['baseUrl']);
		$this->assertEquals('http://localhost/subfolder/', $request['url']);
	}
	
	public function testParsedUrlDifferentFromRequestedUrl(): void
	{
		$_SERVER['REQUEST_URI'] = '/foo/';
		$request = noop::parseRequest($_SERVER);

		$this->assertEquals('http://localhost/foo', $request['parsedUrl']);
		$this->assertEquals('http://localhost/foo/', $request['url']);
	}

	public function testAjax(): void
	{
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$request = noop::parseRequest($_SERVER);

		$this->assertTrue($request['ajax']);
	}

	public function testJson(): void
	{
		$_SERVER['CONTENT_TYPE'] = 'application/json';
		$request = noop::parseRequest($_SERVER);

		$this->assertArrayHasKey('json', $request);
	}
}
