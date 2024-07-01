<?php

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class NoopViewTest extends TestCase
{

	function testView()
	{
		$root = vfsStream::setup('view', null, [
			'view.php' => 'foo'
		]);
		$rootUrl = $root->url(); // vfs://control
		noop::$var['config']['path']['view'] = $rootUrl;

		$output = noop::view('view', NULL);
		$this->assertEquals('foo', $output);
	}

	function testViewWithDatas()
	{
		$root = vfsStream::setup('view', null, [
			'view.php' => '<?php echo $data;'
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['view'] = $rootUrl;

		$output = noop::view('view', 'view');
		$this->assertEquals('view', $output);
	}

	function testSameViewWithDifferentDatas()
	{
		$root = vfsStream::setup('view', null, [
			'view.php' => '<?php echo $data;'
		]);
		$rootUrl = $root->url();
		noop::$var['config']['path']['view'] = $rootUrl;

		$output1 = noop::view('view', hrtime(TRUE));
		$output2 = noop::view('view', hrtime(TRUE));
		$this->assertNotEquals($output1, $output2);
	}
}
