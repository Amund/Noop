<?php

use PHPUnit\Framework\TestCase;

class NoopParseEnvTest extends TestCase {
	
	function testEnv() {
		$arr = [
            'NOT_STARTING_WITH_NOOP_' => '1',
            'NOOP_FOO' => 'bar',
            'NOOP_CONFIG_PATH_VIEW' => 'newFolder',
        ];
		$expected = [
            'foo' => 'bar',
            'config'=> [
                'path' =>[
                    'view' => 'newFolder',
                ],
            ],
        ];
		$this->assertEquals($expected, noop::parseEnv($arr));
	}
	
    function testConvertBooleanStrings() {
        $arr = [
            'NOOP_TESTTRUE' => 'true',
            'NOOP_TESTFALSE' => 'false',
        ];
        $expected = [
            'testtrue'=> true,
            'testfalse'=> false,
        ];
        $this->assertEquals($expected, noop::parseEnv($arr));
    }
}
