<?php

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class NoopLogTest extends TestCase
{

    public function testLogDefaultConfig(): void
    {
        $this->assertEquals(FALSE, noop::$var['config']['log']['active']);
        $this->assertEquals('noop.log', noop::$var['config']['log']['path']);
        $this->assertEquals('emergency', noop::$var['config']['log']['level']);
    }

    public function testLogActivation(): void
    {
		$root = vfsStream::setup('log', null, []);
        $logFile = $root->url().'/noop.log';

		noop::$var['config']['log']['path'] = $logFile;
        noop::$var['config']['log']['active'] = FALSE;
        noop::$var['config']['log']['level'] = 'emergency';

		$this->assertFileDoesNotExist($logFile);
        
		noop::log('emergency', 'foo');
		$this->assertFileDoesNotExist($logFile);

        noop::$var['config']['log']['active'] = TRUE;
		noop::log('emergency', 'foo');
		$this->assertFileExists($logFile);
    }

    public function testLogLevel(): void
    {
        $root = vfsStream::setup('log', null, []);
        $logFile = $root->url().'/noop.log';
        
        noop::$var['config']['log']['path'] = $logFile;
        noop::$var['config']['log']['active'] = TRUE;
        noop::$var['config']['log']['level'] = 'error';
        
        noop::log('warning', 'foo');
		$this->assertFileDoesNotExist($logFile);

        noop::log('error', 'foo');
        $this->assertFileExists($logFile);
    }
}
