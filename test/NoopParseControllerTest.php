<?php

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class NoopParseControllerTest extends TestCase
{

	public function testHasConfigDefaultController(): void
	{
		$this->assertEquals(
			'index',
			noop::$var['config']['default']['controller'],
		);
	}

	public function test01(): void
	{
		$root = vfsStream::setup('control', null, ['index.php' => '']);
		$rootUrl = $root->url(); // vfs://control
		noop::$var['config']['path']['controller'] = $rootUrl;

		$controller = noop::parseController('');
		$this->assertEquals([
			'file' => $rootUrl . '/index.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('trail');
		$this->assertEquals([
			'file' => NULL,
			'trail' => 'trail',
		], $controller);
	}

	public function test02(): void
	{
		$root = vfsStream::setup('control', null, ['controller.php' => '']);
		$rootUrl = $root->url();
		noop::$var['config']['path']['controller'] = $rootUrl;

		//noop::_parseController('');
		//noop::_parseController('trail');

		$controller = noop::parseController('controller');
		$this->assertEquals([
			'file' => $rootUrl . '/controller.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('controller/trail');
		$this->assertEquals([
			'file' => $rootUrl . '/controller.php',
			'trail' => 'trail',
		], $controller);
	}

	public function test03(): void
	{
		$root = vfsStream::setup('control', null, [
			'trail' => [],
			'index.php' => '',
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['controller'] = $rootUrl;

		$controller = noop::parseController('index');
		$this->assertEquals([
			'file' => $rootUrl . '/index.php',
			'trail' => '',
		], $controller);

		//noop::_parseController('trail');

		$controller = noop::parseController('index/trail');
		$this->assertEquals([
			'file' => $rootUrl . '/index.php',
			'trail' => 'trail',
		], $controller);
	}

	// public function test04(): void {
	// 	$path =  __DIR__.'/fixtures/test04';
	// 	noop::set( 'config/path/controller', $path );

	// 	//noop::_parseController('');
	// 	//noop::_parseController('trail');
	// 	//noop::_parseController('sub');
	// }

	public function test05(): void
	{
		$root = vfsStream::setup('control', null, [
			'index.php' => '',
			'controller.php' => '',
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['controller'] = $rootUrl;

		$controller = noop::parseController('');
		$this->assertEquals([
			'file' => $rootUrl . '/index.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('controller');
		$this->assertEquals([
			'file' => $rootUrl . '/controller.php',
			'trail' => '',
		], $controller);
	}

	public function test06(): void
	{
		$root = vfsStream::setup('control', null, [
			'sub' => [],
			'controller.php' => '',
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['controller'] = $rootUrl;

		$controller = noop::parseController('controller');
		$this->assertEquals([
			'file' => $rootUrl . '/controller.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('controller/sub');
		$this->assertEquals([
			'file' => $rootUrl . '/controller.php',
			'trail' => 'sub',
		], $controller);

		$controller = noop::parseController('controller/trail');
		$this->assertEquals([
			'file' => $rootUrl . '/controller.php',
			'trail' => 'trail',
		], $controller);

		//noop::_parseController('');
		//noop::_parseController('sub');
	}

	public function test07(): void
	{
		$root = vfsStream::setup('control', null, [
			'index' => [],
			'index.php' => '',
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['controller'] = $rootUrl;

		$controller = noop::parseController('');
		$this->assertEquals([
			'file' => $rootUrl . '/index.php',
			'trail' => '',
		], $controller);

		//noop::_parseController('trail');
		//noop::_parseController('sub');
	}

	public function test08(): void
	{
		$root = vfsStream::setup('control', null, [
			'sub' => [
				'controller.php' => '',
			],
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['controller'] = $rootUrl;

		$controller = noop::parseController('sub/controller');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/controller.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('sub/controller/trail');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/controller.php',
			'trail' => 'trail',
		], $controller);

		//noop::_parseController('sub');
		//noop::_parseController('sub/trail');
	}

	public function test09(): void
	{
		$root = vfsStream::setup('control', null, [
			'sub' => [
				'sub' => [
					'controller.php' => '',
				],
			],
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['controller'] = $rootUrl;

		$controller = noop::parseController('sub/sub/controller');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub/controller.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('sub/sub/controller/trail');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub/controller.php',
			'trail' => 'trail',
		], $controller);

		//noop::_parseController('sub');
		//noop::_parseController('sub/trail');
		//noop::_parseController('sub/sub');
		//noop::_parseController('sub/sub/trail');
	}

	public function test10(): void
	{
		$root = vfsStream::setup('control', null, [
			'sub' => [
				'sub' => [
					'controller.php' => '',
				],
			],
			'sub.php' => '',
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['controller'] = $rootUrl;

		$controller = noop::parseController('sub/sub/controller');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub/controller.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('sub/sub/controller/trail');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub/controller.php',
			'trail' => 'trail',
		], $controller);

		$controller = noop::parseController('sub');
		$this->assertEquals([
			'file' => $rootUrl . '/sub.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('sub/trail');
		$this->assertEquals([
			'file' => $rootUrl . '/sub.php',
			'trail' => 'trail',
		], $controller);

		$controller = noop::parseController('sub/1/2');
		$this->assertEquals([
			'file' => $rootUrl . '/sub.php',
			'trail' => '1/2',
		], $controller);

		$controller = noop::parseController('sub/sub');
		$this->assertEquals([
			'file' => $rootUrl . '/sub.php',
			'trail' => 'sub',
		], $controller);

		//noop::_parseController('sub/sub');
		//noop::_parseController('sub/sub/trail');
	}

	public function test11(): void
	{
		$root = vfsStream::setup('control', null, [
			'sub' => [
				'sub' => [
					'controller.php' => '',
				],
				'sub.php' => '',
			],
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['controller'] = $rootUrl;

		$controller = noop::parseController('sub/sub/controller');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub/controller.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('sub/sub/controller/trail');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub/controller.php',
			'trail' => 'trail',
		], $controller);

		$controller = noop::parseController('sub/sub');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('sub/sub/trail');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub.php',
			'trail' => 'trail',
		], $controller);

		$controller = noop::parseController('sub/sub/1/2');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub.php',
			'trail' => '1/2',
		], $controller);

		//noop::_parseController('sub');
		//noop::_parseController('sub/trail');
	}

	public function test12(): void
	{
		$root = vfsStream::setup('control', null, [
			'sub' => [
				'sub' => [
					'controller.php' => '',
				],
				'sub.php' => '',
			],
			'sub.php' => '',
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['controller'] = $rootUrl;

		$controller = noop::parseController('sub/sub/controller');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub/controller.php',
			'trail' => '',
		], $controller);

		$controller = noop::parseController('sub/sub/controller/trail');
		$this->assertEquals([
			'file' => $rootUrl . '/sub/sub/controller.php',
			'trail' => 'trail',
		], $controller);
	}
}
