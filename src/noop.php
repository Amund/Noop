<?php

// 2024 - NOOP 4.0.0
// 2017 - NOOP 3.0.1
// 2017 - NOOP 3.0.0
// 2016 - NOOP 2.0.3
// 2015 - NOOP 2.0.2
// 2014 - NOOP 2.0.1
// 2013 - NOOP 2.0
// 2010 - NOOP 1.0

// MIT License
// Copyright (c) 2010 Dimitri Avenel

// Permission is hereby granted, free of charge, to any person obtaining
// a copy of this software and associated documentation files (the
// "Software"), to deal in the Software without restriction, including
// without limitation the rights to use, copy, modify, merge, publish,
// distribute, sublicense, and/or sell copies of the Software, and to
// permit persons to whom the Software is furnished to do so, subject to
// the following conditions:

// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
// LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
// OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
// WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

class NoopException extends Exception
{
}
class NoopConfigException extends NoopException
{
}
class NoopRequestException extends NoopException
{
}
class NoopRouteException extends NoopException
{
}
class NoopViewException extends NoopException
{
}

class noop
{
	// All registry in an associative array
	public static $var = [
		'config' => [ // default config
			'default' => [
				'controller' => 'index',
				'lang' => 'en',
				'mime' => 'html',
				'handler' => [
					'error' => ['noop', '_error_handler'],
					'exception' => ['noop', '_exception'],
				]
			],
			'path' => [
				'controller' => 'src/control',
				'model' => 'src/model',
				'view' => 'src/view',
			],
			'mime' => [
				'text' => 'text/plain; charset=UTF-8',
				'html' => 'text/html; charset=UTF-8',
				'json' => 'application/json; charset=UTF-8',
			],
			'pdo' => [],
			'log' => [
				'active' => FALSE,
				'path' => 'noop.log',
				'level' => 'emergency',
			],
			'dev' => [
				'active' => FALSE,
				'benchmark' => TRUE,
				'inspect' => '<pre style="font:12px/13px Consolas,\'Lucida Console\',monospace;text-align:left;color:#ddd;background-color:#222;padding:5px;overflow:auto;">%s</pre>',
			],
		],
		'events' => [],
		'request' => [], // parsed request related vars
		'routes' => [], // declared routes
		'route' => NULL, // current route
		'benchmark' => [], // collection of benchmarks
		'pdo' => [], // pdo connections objects
		'var' => [], // other user vars...
	];
	const LOG_LEVELS = [
		'emergency' => 0,
		'alert' => 1,
		'critical' => 2,
		'error' => 3,
		'warning' => 4,
		'notice' => 5,
		'info' => 6,
		'debug' => 7,
	];

	// Launch Noop app
	public static function start(array $config = [])
	{
		noop::event('beforeStart');
		
		set_error_handler(function ($code, $msg, $file, $line) {
			throw new ErrorException($msg, $code, 1, $file, $line);
		});

		noop::event('beforeConfig');
		self::$var = noop::extend(self::$var, self::parseEnv($_ENV));
		self::$var['config'] = noop::extend(self::$var['config'], $config);
		noop::event('afterConfig');

		ini_set('display_errors', (bool) self::$var['config']['dev']['active']);

		// self::benchmark('Page', TRUE);

		noop::event('beforeRequest');
		self::$var['request'] = self::parseRequest($_SERVER);
		// if( $request_uri != self::get( 'app/dir' ).$canonical )
		// 	self::redirect( self::get( 'app/url' ).$canonical );

		self::$var['controller'] = self::parseController(self::$var['request']['path'] ?? '');
		// noop::event('beforeRoute');
		// noop::$var['route'] = self::route(self::$var['request']['path'] ?? '');
		// var_dump(noop::$var['events']);
		// call_user_func($route['callback'], ...$route['matches']);
		// var_dump(self::$var['request']['controller']);
		

		ob_start();

		$controller = &self::$var['controller']['file'] ?? NULL;
		if ($controller !== NULL) {
			// var_dump(noop::$var['controller']);
			// die();
			require_once $controller;
		} else {
			throw new NoopControllerException(
				'Controller "' . $controller['path'] . '" not found',
				404
			);
		}

		// Send response, if not already done
		self::output(NULL, self::$var['config']['default']['mime'], FALSE);

		// die( 'WTF?' );
	}

	/**
	 * Returns a PDO object for the specified database connection. If the connection
	 * has already been established, it returns the cached object. If the connection
	 * is not configured, it throws a NoopConfigException. If the connection cannot
	 * be established, it throws a NoopConfigException.
	 *
	 * @param string $name The name of the database connection.
	 * @param mixed $pdo_options (optional) PDO options.
	 * @throws NoopConfigException If the connection is not configured or cannot be established.
	 * @return PDO The PDO object for the specified database connection.
	 */
	public static function pdo($name, $pdo_options = NULL)
	{
		// existing cached pdo object
		if (isset(self::$var['pdo'][$name]))
			return self::$var['pdo'][$name];
		// new pdo object
		if (empty(self::$var['config']['pdo'][$name]))
			throw new NoopConfigException('Unknown "' . $name . '" database');
		$param = explode(',', self::$var['config']['pdo'][$name]);
		switch ($param[0]) {
			case 'mysql':
				self::$var['pdo'][$name] = new PDO('mysql:' . $param[1], $param[2], $param[3]);
				self::$var['pdo'][$name]->query('SET NAMES "UTF8"');
				break;
			case 'sqlite':
				$path = realpath($param[1]);
				if ($path === FALSE)
					throw new NoopConfigException('"' . $name . '" database not found');
				self::$var['pdo'][$name] = new PDO('sqlite:' . $path);
				break;
			default:
				throw new NoopConfigException('Bad "' . $name . '" database configuration');
		}
		return self::$var['pdo'][$name];
	}

	/**
	 * Output the content with the specified content type.
	 *
	 * @param mixed $content The content to output. If null, the global output buffer will be used.
	 * @param string|null $type The content type. If null, the default content type will be used.
	 * @return void
	 */
	public static function output($content = NULL, $type = NULL)
	{
		if (is_null($content)) { // use the global buffer...
			$content = ob_get_clean();
		} else { // ...or start with an empty buffer
			ob_end_clean();
		}
		ob_start();
		if (preg_match('#.+/.+#', $type)) {
			$mime = $type;
		} else {
			$defaultType = self::get('config/default/type');
			$type = (is_null($type) ? $defaultType : $type);
			$type = (is_null($type) ? 'html' : $type);
			$mime = self::get('config/mime/' . $type);
			$mime = (is_null($mime) ? 'text/plain' : $mime);
		}
		header('Content-Type: ' . $mime);
		// Send final result
		echo $content;
		die();
	}

	/**
	 * Redirects the user to a specified URL.
	 *
	 * @param string $url The URL to redirect to.
	 * @param int $code The HTTP status code to use for the redirect. Defaults to 302.
	 * @return void
	 */
	public static function redirect($url, $code = 302)
	{
		header('Location: ' . $url, TRUE, $code);
		die();
	}

	/**
	 * Sets the HTTP status code and outputs a status message.
	 *
	 * @param int $code The HTTP status code.
	 * @param string $status The status message.
	 * @param string $content The content to output. Defaults to an empty string.
	 * @param string $type The content type. Defaults to 'html'.
	 * @return void
	 */
	public static function status($code, $status, $content = '', $type = 'html')
	{
		header(self::$var['request']['protocol'] . ' ' . $code . ' ' . utf8_decode($status));
		self::output((empty($content) ? $status : $content), $type);
	}

	/**
	 * Store or retrieve benchmarking data.
	 *
	 * @param string $name The name of the benchmark.
	 * @param bool|null $action Whether to start or stop the benchmark.
	 * @return string|null The time taken for the benchmark, formatted to 6 decimal places.
	 */
	public static function benchmark(string $name, bool $action = NULL)
	{
		// store tics...
		if (!is_null($action)) {
			if ($action) {
				unset(self::$var['benchmark'][$name]['stop']);
				self::$var['benchmark'][$name]['start'] = microtime(TRUE);
			} else {
				self::$var['benchmark'][$name]['stop'] = microtime(TRUE);
			}
		} else {
			// ... or return time
			$time = self::$var['benchmark'][$name]['stop'] - self::$var['benchmark'][$name]['start'];
			return  number_format(round($time, 6), 6);
		}
	}

	/**
	 * Access the noop::$var array from a path, or in arbitraty $arr array.
	 *
	 * @param string $path The path to the variable, slash separated.
	 * @param array $arr The array to access the variable from. Defaults to noop::$var.
	 * @return mixed|null Returns the value of the variable if it exists, NULL otherwise.
	 */
	public static function get(string $path, array $arr = NULL)
	{
		if ($arr === NULL) $arr = &self::$var;
		$path = trim($path, '/');
		if (empty($path)) return $arr;
		$current = &$arr;
		foreach (explode('/', $path) as $segment) {
			if (!isset($current[$segment])) return NULL;
			$current = &$current[$segment];
		}
		return $current;
	}

	/**
	 * Assign a variable in noop::$var array from a path, or in arbitraty $arr array.
	 *
	 * @param string $path The path to the variable, slash separated.
	 * @param mixed $value The value to assign to the variable.
	 * @param array $arr The array to assign the variable to. Defaults to noop::$var.
	 * @return bool Returns TRUE if the value was assigned, FALSE otherwise.
	 */
	public static function set(string $path, $value, array &$arr = NULL)
	{
		$path = trim($path, '/');
		if ($arr === NULL) $arr = &self::$var;
		$current = &$arr;
		foreach (explode('/', $path) as $segment) {
			if (!isset($current[$segment])) $current[$segment] = array();
			$current = &$current[$segment];
		}
		$current = $value;
		return TRUE;
	}

	/**
	 * Delete a variable in noop::$var from a path and return its value, or in arbitraty $arr array.
	 *
	 * @param string $path The path to the variable, slash separated.
	 * @param array &$arr The array to delete the variable from. Defaults to noop::$var.
	 * @return mixed|null The value of the deleted variable, or NULL if the variable does not exist.
	 */
	public static function del(string $path, array &$arr = NULL)
	{
		$path = trim($path, '/');
		if ($arr === NULL) $arr = &self::$var;
		$current = &$arr;
		$segments = explode('/', $path);
		foreach ($segments as $segment) {
			if (!isset($current[$segment])) return NULL;
			$last = &$current;
			$current = &$current[$segment];
		}
		$out = $current;
		unset($last[$segment]);
		return $out;
	}

	/**
	 * Renders a php view file and returns its content.
	 *
	 * @param string $name The name of the view file, without extension, and relative to config/path/view.
	 * @param mixed $data The data to be passed to the view file. Defaults to NULL.
	 * @throws NoopViewException If the view file is not found.
	 * @return string The rendered content of the view file.
	 */
	public static function view(string $name, $data = NULL)
	{
		$path = self::$var['config']['path']['view'] . '/' . $name . '.php';
		if (!is_file($path) || !is_readable($path)) {
			throw new NoopViewException('View "' . $name . '" not found');
		} else {
			return (function ($path, $data) {
				ob_start();
				require $path;
				return ob_get_clean();
			})($path, $data);
		}
	}

	/**
	 * Parses an array and returns a new array with the values organized according to the keys that start with 'NOOP_'.
	 * Useful for parsing environment variables from $_ENV and overriding the noop::$var['config'] array.
	 *
	 * @param array $arr The array to be parsed.
	 * @return array The parsed array with values organized according to the keys that start with 'NOOP_'.
	 */
	public static function parseEnv(array $arr): array
	{
		$output = [];
		foreach ($arr as $key => $value) {
			if (!str_starts_with(strtoupper($key), 'NOOP_')) continue;
			$key = substr($key, 5);
			$current = &$output;
			foreach (explode('_', $key) as $segment) {
				$current = &$current[strtolower($segment)];
			}
			if (strtolower($value) === 'true') $value = true;
			if (strtolower($value) === 'false') $value = false;
			$current = $value;
		}
		return $output;
	}

	/**
	 * Parses the server request and populates the noop::$var['request'] array with the parsed values.
	 *
	 * @return array The parsed request array.
	 */
	public static function parseRequest($server): array
	{
		$method = $server['REQUEST_METHOD'] ?? '';

		if (
			isset($server['HTTP_X_FORWARDED_PROTO']) &&
			$server['HTTP_X_FORWARDED_PROTO'] === 'https'
		) {
			$server['HTTPS'] = 'on';
		}
		$protocol = isset($server['HTTPS']) && $server['HTTPS'] == 'on' ? 'https' : 'http';

		$host = $server['HTTP_HOST'] ?? '';
		$port = $server['SERVER_PORT'] ?? '';
		$uri = $server['REQUEST_URI'] ?? ''; // rawurldecode($server['REQUEST_URI'])

		$basePath = $server['SCRIPT_NAME'] ?? '';
		$basePath = dirname($basePath);
		$basePath = $basePath === '/' ? '' : $basePath;

		$path = preg_replace('/\\?.*/', '', $uri);
		$path = substr($path, strlen($basePath));
		$path = $path === '/' ? $path : rtrim($path, '/');

		$qs = strpos($uri, '?') !== FALSE
			? preg_replace('/.*\\?/', '', $uri)
			: '';

		$baseUrl = strtr('[protocol]://[host][port][basePath]', [
			'[protocol]' => $protocol,
			'[host]' => $host,
			'[port]' => in_array($port, [80, 443]) ? '' : ':' . $port,
			'[basePath]' => $basePath,
		]);

		$parsedUrl = strtr('[baseUrl][path][qs]', [
			'[baseUrl]' => $baseUrl,
			'[path]' => $path,
			'[qs]' => $qs === '' ? '' : '?' . $qs,
		]);

		$url = strtr('[protocol]://[host][port][uri]', [
			'[protocol]' => $protocol,
			'[host]' => $host,
			'[port]' => in_array($port, [80, 443]) ? '' : ':' . $port,
			'[uri]' => $uri,
		]);

		// mandatory keys
		$request = [
			'method' => $method,
			'protocol' => $protocol,
			'host' => $host,
			'port' => $port,
			'uri' => $uri,
			'basePath' => $basePath,
			'baseUrl' => $baseUrl,
			'path' => $path,
			'qs' => $qs,
			'parsedUrl' => $parsedUrl,
			'url' => $url,
		];

		// optional keys
		if (
			isset($server['HTTP_X_REQUESTED_WITH']) &&
			strtolower($server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
		) {
			$request['ajax'] = TRUE;
		}

		if (
			isset($server['CONTENT_TYPE']) &&
			substr($server['CONTENT_TYPE'], 0, 16) == 'application/json'
		) {
			$request['json'] = json_decode(file_get_contents('php://input'), TRUE);
		}

		return $request;
	}

	/**
	 * Parses the controller path and returns an array containing the file path and any trailing segments.
	 *
	 * @param string $path The controller path to parse (default: '')
	 * @throws NoopConfigException If the controller path is empty or the controller path directory does not exist
	 * @return array An array containing the file path and any trailing segments
	 *               - 'file' (string): The file path of the controller
	 *               - 'trail' (string): Any trailing segments after the controller
	 */
	public static function parseController(string $path = '')
	{
		$configDefaultController = self::$var['config']['default']['controller'];
		$configPathController    = rtrim(self::$var['config']['path']['controller'], '/');

		$segments = trim($path, '/');
		$segments = ($segments === '' ? $configDefaultController : $segments);

		if ($segments === '' || !is_dir($configPathController))
			throw new NoopConfigException('Controller path not found');

		$segments = explode('/', $segments);
		$controller = NULL;
		$path = '';
		$lastfile = '';
		$trail = '';

		foreach ($segments as $segment) {
			$path .= '/' . $segment;
			$dir = $configPathController . $path;
			$file = $dir . '.php';
			if (is_file($file) && is_readable($file)) {
				$controller = $file;
				$lastfile = $path;
				$trail = '';
			} else {
				$trail .= '/' . $segment;
			}
		}
		$path = $lastfile;

		$file = $dir . '/' . $configDefaultController . '.php';
		if (is_file($file)) {
			$controller = $file;
		}

		return [
			'file' => $controller,
			'trail' => trim($trail, '/'),
		];
	}

	public static function route(string $str, callable &$callback = NULL)
	{
		if ($callback !== NULL) {
			// add route
			self::$var['routes'][] = [
				'regex' => $str,
				'callback' => $callback,
			];
		} else {
			// find route
			$filter = fn ($key) => !is_numeric($key);
			foreach (self::$var['routes'] as $index => $route) {
				if (preg_match($route['regex'], $str, $matches)) {
					$matches = array_filter($matches, $filter, ARRAY_FILTER_USE_KEY);
					return [
						'index' => $index,
						'regex' => &$route['regex'],
						'callback' => &$route['callback'],
						'params' => $matches,
					];
				}
			}
			return NULL;
		}
		// if($callback === NULL) {
		// 	echo "Route not found : 404";
		// } else {
		// 	call_user_func($callback, ...$matches);
		// }
	}

	/**
	 * Inspects the given path in the array and returns a formatted string representation of the data.
	 *
	 * @param string $path The path to inspect in the array. Defaults to an empty string.
	 * @param array|null $arr The array to inspect. If not provided, uses the static variable `self::$var`.
	 * @return string The formatted string representation of the data at the given path.
	 */
	public static function inspect($path = '', $arr = NULL)
	{
		if ($arr === NULL)
			$arr = &self::$var;
		return sprintf(self::$var['config']['dev']['inspect'], print_r(self::get($path, $arr), TRUE));
	}

	/**
	 * Generates a hash signature using the specified algorithm and data.
	 *
	 * @param string $algo The name of the hashing algorithm to use.
	 * @param mixed ...$data The data to be hashed.
	 * @return string The generated hash signature.
	 */
	public static function sign($algo, ...$data): string
	{
		return hash($algo, implode('|', array_map('serialize', $data)));
	}

	/**
	 * Triggers an event and executes all associated callbacks, or adds a new callback to the events list.
	 *
	 * @param string $event The name of the event to trigger.
	 * @param callable|null $callback The callback function to execute or add to the event. Optional.
	 * @param mixed ...$args The arguments to pass to the callback function. Optional.
	 * @return void
	 */
	public static function event(string $event, string $description = NULL, $callback = NULL, ...$args): void
	{
		if ($description === NULL && $callback === NULL) {
			if (!isset(self::$var['events'][$event])) return;
			foreach (self::$var['events'][$event] as $i => $item) {
				$description = array_shift($item);
				self::log('debug', 'Execute event: ' . $event . ' ' . $i . ' ' . $description);
				call_user_func(...$item);
			}
		} else {
			if (!isset(self::$var['events'][$event])) self::$var['events'][$event] = [];
			$i = count(self::$var['events'][$event]);
			self::log('debug', 'Add event: ' . $event . ' ' . $i . ' ' . $description);
			self::$var['events'][$event][] = [$description, $callback, ...$args];
		}
	}

	/**
	 * Logs a message with the specified level if logging is enabled in the configuration.
	 *
	 * @param string $level The level of the log message. Must be one of the following: 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'.
	 * @param string $message The log message to be logged.
	 * @return void
	 */
	public static function log(string $level, string $message): void
	{
		if (noop::$var['config']['log']['active']) {
			$level = strtolower($level);
			$configLevel = strtolower(noop::$var['config']['log']['level']);
			$levels = array_keys(self::LOG_LEVELS);
			if (in_array($level, $levels) && in_array($configLevel, $levels)) {
				$levelInt = self::LOG_LEVELS[$level];
				$configLevelInt = self::LOG_LEVELS[$configLevel];
				if ($levelInt <= $configLevelInt) {
					$message = strtr('{time} {level} - {message}' . "\n", [
						'{time}' => date('Y-m-d H:i:s'),
						'{level}' => strtoupper($level),
						'{message}' => $message,
					]);
					$file = noop::$var['config']['log']['path'];
					file_put_contents($file, $message, FILE_APPEND);
				}
			}
		}
	}

	/**
	 * Extends an array with the values of another array.
	 *
	 * @param array $a The array to be extended.
	 * @param array $b The array containing the values to extend with.
	 * @return array The extended array.
	 */
	public static function extend(array $a, array $b)
	{
		foreach ($b as $k => $v) {
			if (is_array($v)) {
				if (!isset($a[$k])) {
					$a[$k] = $v;
				} else {
					$a[$k] = self::extend($a[$k], $v);
				}
			} else {
				$a[$k] = $v;
			}
		}
		return $a;
	}
}
