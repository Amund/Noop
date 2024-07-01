<?php

use PHPUnit\Framework\TestCase;

class NoopRouteTest extends TestCase
{
    public function setUp(): void
    {
        noop::$var['routes'] = [];
        noop::$var['var'] = '';
    }

    function testAddRoute()
    {
        $callback = fn () => NULL;
        noop::route('#/#', $callback);

        $this->assertEquals(
            [
                'regex' => '#/#',
                'callback' => $callback,
            ],
            noop::$var['routes'][0]
        );
    }

    function testLoadBasicRoute()
    {
        $callback = fn () => NULL;
        noop::$var['routes'][0] = [
            'regex' => '#/#',
            'callback' => $callback,
        ];

        $route = noop::route('/');
        $this->assertEquals([
            'index' => 0,
            'regex' => '#/#',
            'callback' => $callback,
            'params' => [],
        ], $route);
    }

    function testLoadParametizedRoute()
    {
        $callback = fn () => NULL;
        noop::$var['routes'][0] = [
            'regex' => '#/blog/(?P<slug>[^/]+)#',
            'callback' => $callback,
        ];

        $route = noop::route('/blog/foo');
        $this->assertEquals([
            'index' => 0,
            'regex' => '#/blog/(?P<slug>[^/]+)#',
            'callback' => $callback,
            'params' => ['slug' => 'foo'],
        ], $route);
    }

    function testLoadMultiParametizedRoute()
    {
        $callback = fn () => NULL;
        noop::$var['routes'][] = [
            'regex' => '#/blog/(?P<id>\d+)-(?P<slug>[^/]+)#',
            'callback' => $callback,
        ];

        $route = noop::route('/blog/123-foo');
        $this->assertEquals([
            'index' => 0,
            'regex' => '#/blog/(?P<id>\d+)-(?P<slug>[^/]+)#',
            'callback' => $callback,
            'params' => ['slug' => 'foo', 'id' => '123'],
        ], $route);
    }
}
