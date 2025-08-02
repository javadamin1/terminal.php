<?php

if ( session_status() === PHP_SESSION_NONE ) {
	session_start();
}

/**
 * Terminal.php - Terminal Emulator for PHP
 *
 * @package  Terminal.php
 * @author   SmartWF <hi@smartwf.ir>
 */

/* Choose a random key Like ('YourRandomSecureKey') for Security */
const VERSION = '1.1.0';
const KEY     = 'YourRandomSecureKey';

if ( KEY !== '' ) {
	$userKey = $_GET['key'] ?? null;
	if ( $userKey !== KEY ) {
		$_SESSION['key_attempts'] = ($_SESSION['key_attempts'] ?? 0) + 1;

		if ( $_SESSION['key_attempts'] > 3 ) {
			http_response_code(429);
			exit('Too many invalid attempts. Try again later.');
		}

		http_response_code(403);
		exit('Access denied.');
	}

	unset($_SESSION['key_attempts']);
}

if ( (KEY !== '' && !isset($_GET['key'])) || (KEY !== '' && isset($_GET['key']) && $_GET['key'] !== KEY) ) {
	header('location: /');
	exit();
}

if ( !function_exists('shell_exec') ) {
	exit('Sorry, this server has blocked shell access :(');
}

/**
 *  config
 */
$config = [
	'laravelMode'     => false,
	'cacheFile'       => __DIR__ . '/cache/cache.json',
	'envFile'         => __DIR__ . '/.terminal_env',
	'temporaryCache'  => 'cookie', // none,cookie,session
	'tools'           => [
		'cache'        => 'month',    // forever,day,week,month
		'useful'       => [ // tools list for search in install tools
			'git',
			'composer',
			'php',
			'npm',
			'node',
			'yarn',
			'curl',
			'wget',
			'htop',
			'top',
			'ping',
			'vim',
			'nano',
			'ssh',
			'scp',
			'zip',
			'unzip',
			'tar',
			'make',
			'gcc',
			'git-lfs',
			'python3',
			'pip3',
			'telnet',
			'gzip',
			'g++'
		],
		'repoPath'     => __DIR__ . '/terminal-repo.json',
		'downloadPath' => __DIR__ . '/tools/download',
		'binPath'      => __DIR__ . '/tools/bin',
	],
	'blockedCommands' => [/*'mkdir',
        'rm',
        'git',
        'wget',
        'curl',
        'chmod',
        'rename',
        'mv',
        'cp'*/
	],
	'checkUpdate'     => 'day', // none,day,week,month
	'debugMode'       => true
];

class CustomCommands {

	/***************************************************************
	 *                 Add Your Custom Command Here                *
	 ***************************************************************
	 *    note 1: Function Name is Command and return is Result    *
	 *    note 2: $a is array of arguments                         *
	 * *************************************************************/

	public static function hi ($a) : string {
		return 'Hi ' . implode(' ', $a);
	}

	public static function md5 ($a) : string {
		$input = implode(' ', $a);
		if ( $input ) {
			return md5($input);
		} else {
			return 'write something, example:<br>md5 test';
		}
	}

	public static function developer () : string {
		return 'SmartWF<br>
                        <a href="https://github.com/smartwf" target="_blank">github (original)</a> &nbsp; &nbsp;
                        <a href="mailto:hi@smartwf.ir" target="_blank">mail (original)</a> &nbsp; &nbsp;
                        <a href="http://twitter.com/smartwf" target="_blank">twitter (original)</a>
                        <br><br>
                        Forked and maintained by <strong>Javad Fathi</strong><br>
                        <a href="https://github.com/javadamin1" target="_blank">github</a> &nbsp; &nbsp;
                        <a href="mailto:javadamin93@gmail.com" target="_blank">mail</a>
                        <a href="https:linkedin.com/in/javadfathi" target="_blank">Linkedin</a>
                        <a href="https:javadfathi.ir" target="_blank">Web Site</a>';
	}


}

class Helper {

	public static function removeSpecialChar ($string) {
		if ( empty($string) ) {
			return $string;
		}
		$str = '/[^A-Za-z0-9]/i';

		return preg_replace($str, '', $string); // Removes special chars.
	}


	/**
	 * @param      $data
	 * @param bool $flag false for dont die after print_r
	 *
	 * @return void
	 */
	public static function dd (...$data) {
		echo '<div style="padding: 1rem;"><pre>';

		foreach ( $data as $item ) {
			echo "<div style='background: #121212;color: #9ad114;padding: 1% 2%;border-radius: 10px;' >";
			print_r($item);
			echo '</div>';
			echo '<br>';
		}
		echo '</pre></div>';

		die();
	}

	/**
	 * @param      $data
	 * @param bool $flag false for dont die after print_r
	 *
	 * @return void
	 */
	public static function dump (...$data) {
		echo '<div style="padding: 1rem;"><pre>';

		foreach ( $data as $item ) {
			echo "<div style='background: #121212;color: #9ad114;padding: 1% 2%;border-radius: 10px;' >";
			print_r($item);
			echo '</div>';
			echo '<br>';
		}
		echo '</pre></div>';
	}

	/**
	 * @param      $data
	 * @param bool $flag false for dont die after var_dump
	 *
	 * @return void
	 */
	public static function vd ($data, bool $flag = true) {
		echo '<div style="padding: 1rem;"><pre>';
		echo "<div style='background: #121212;color: #9ad114;padding: 1% 2%;border-radius: 10px;' >";
		var_dump($data);
		echo '</div>';
		echo '<br>';
		echo '</pre></div>';

		if ( $flag ) {
			die();
		}
	}

	/**
	 * @param string $data
	 *
	 * @return string
	 */
	public static function encode (?string $data) : string {
		if ( $data ) {
			return '';
		}
		$rotated = str_rot13($data);
		$hexed   = bin2hex($rotated);

		return strrev($hexed);
	}

	/**
	 * @param string|null $encoded
	 *
	 * @return string|null
	 */
	public static function decode (?string $encoded) : ?string {
		if ( empty($encoded) ) {
			return null;
		}
		$reversed = strrev($encoded);
		$rotated  = hex2bin($reversed);

		if ( $rotated === false ) {
			return null;
		}

		return str_rot13($rotated);
	}

	public static function fixDir (string $dir) : ?string {
		return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}

	/**
	 *  find os
	 *
	 * @return string
	 */
	public static function detectOS () : string {
		if ( PHP_OS_FAMILY === 'Windows' ) {
			return 'windows';
		}

		if ( file_exists('/etc/alpine-release') ) {
			return 'alpine';
		}

		if ( file_exists('/etc/centos-release') ) {
			return 'centos';
		}

		if ( file_exists('/etc/os-release') ) {
			$release = file_get_contents('/etc/os-release');

			if ( stripos($release, 'rocky') !== false ) {
				return 'rocky';
			}

			if ( stripos($release, 'almalinux') !== false ) {
				return 'alma';
			}

			if ( stripos($release, 'ubuntu') !== false ) {
				return 'ubuntu';
			}

			if ( stripos($release, 'debian') !== false ) {
				return 'debian';
			}

			if ( stripos($release, 'fedora') !== false ) {
				return 'fedora';
			}

			if ( stripos($release, 'arch') !== false ) {
				return 'arch';
			}
		}

		return strtolower(PHP_OS); // fallback: linux, darwin, etc.
	}

	/**
	 * @param string $dir
	 *
	 * @return void
	 * @throws RuntimeException
	 */
	public static function mkdir (string $dir) : void {
		if ( !is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir) ) {
			throw new RuntimeException("❌ Extract error: Failed to create destination directory: " . htmlspecialchars($dir));
		}
	}


}

class Cache {

	private static ?self $instance       = null;
	private ?string      $cacheFile      = '';
	private string       $temporaryCache = '';
	private ?array       $config         = null;

	private function __construct () {
		$this->boot();
	}

	public static function getInstance () : Cache {
		if ( is_null(self::$instance) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function boot () {

		if ( is_null($this->config) ) {
			global $config;
			$cacheFile      = '';
			$temporaryCache = '';
			$this->config   = $config ?? [];

			if ( $this->config && is_array($this->config) && isset($this->config['cacheFile']) ) {
				$cacheFile      = $this->config['cacheFile'];
				$temporaryCache = $this->config['temporaryCache'] ?? '';
			}
			if ( empty($cacheFile) ) {
				$cacheFile = __DIR__ . '/cache/cache.json';
			}

			$this->cacheFile      = $this->ensureCommandsCacheFile($cacheFile);
			$this->temporaryCache = $temporaryCache;
		}

	}

	/**
	 * @param string $filePath crate file and path if not exist
	 *
	 * @return string
	 */
	private function ensureCommandsCacheFile (string $filePath) : string {
		$dirPath = dirname($filePath);

		if ( !is_dir($dirPath) ) {
			mkdir($dirPath, 0775, true);
		}
		if ( !file_exists($filePath) ) {
			file_put_contents($filePath, json_encode([], JSON_PRETTY_PRINT));
		}

		return $filePath;
	}

	private function getData (string $key = '') {
		if ( !empty($this->temporaryCache) ) {
			$cache = '';
			if ( $this->temporaryCache === 'session' && isset($_SESSION['terminalCache']) ) {
				$cache = Helper::decode($_SESSION['terminalCache']);
			} elseif ( $this->temporaryCache === 'cookie' && isset($_COOKIE['terminalCache']) ) {
				$cache = Helper::decode($_COOKIE['terminalCache']);
			}
			if ( !empty($cache) && is_string($cache) ) {
				$data = json_decode($cache, true);
				if ( $data !== false ) {
					return $key ? ($data[$key] ?? null) : $data;
				}
			}
		}
		if ( file_exists($this->cacheFile) ) {
			$json = file_get_contents($this->cacheFile);
			if ( !empty($this->temporaryCache) ) {
				if ( $this->temporaryCache === 'session' ) {
					$_SESSION['terminalCache'] = Helper::encode($json);
				} elseif ( $this->temporaryCache === 'cookie' && isset($_COOKIE['terminalCache']) ) {
					$_COOKIE['terminalCache'] = Helper::encode($json);
				}
			}
			$data = json_decode($json, true);

			return $key ? ($data[$key] ?? null) : $data;
		}
	}

	private function setData ($key, $value) {

		if ( file_exists($this->cacheFile) ) {
			$data        = $this->getData() ?? [];
			$data[$key]  = $value;
			$json        = json_encode($data);
			$fileCreated = @file_put_contents($this->cacheFile, $json);
			if ( $fileCreated !== false ) {
				if ( $this->temporaryCache === 'session' ) {
					$_SESSION['terminalCache'] = Helper::encode($json);
				} elseif ( $this->temporaryCache === 'cookie' && isset($_COOKIE['terminalCache']) ) {
					$_COOKIE['terminalCache'] = Helper::encode($json);
				}
			}

			return $fileCreated;
		}
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	private function deleteData (string $key) : bool {
		if ( file_exists($this->cacheFile) ) {
			$data = $this->getData() ?? [];
			if ( array_key_exists($key, $data) ) {
				unset($data[$key]);
			}
			$json        = json_encode($data);
			$fileCreated = @file_put_contents($this->cacheFile, $json);
			if ( $fileCreated !== false ) {
				if ( $this->temporaryCache === 'session' ) {
					$_SESSION['terminalCache'] = Helper::encode($json);
				} elseif ( $this->temporaryCache === 'cookie' && isset($_COOKIE['terminalCache']) ) {
					$_COOKIE['terminalCache'] = Helper::encode($json);
				}
			}

			return $fileCreated !== false;
		}

		return false;
	}


	/**
	 * Get the option value with caching.
	 *
	 * @param string     $path
	 * @param mixed|null $default
	 * @param bool       $returnModel
	 *
	 * @return mixed
	 */
	public function get (string $path, mixed $default = null, bool $returnModel = false) {
		if ( empty($path) ) {
			return $default;
		}
		$pathList = explode('.', $path);
		$key      = $pathList[0];
		unset($pathList[0]);
		$data = $this->getData($key);
		if ( empty($data) ) {
			return $default;
		}
		if ( empty($pathList) || is_scalar($data) ) {
			return $data;
		}

		return self::resolveOptionValue($data, $pathList, $default);
	}


	/**
	 * Resolve the option value from array data based on path.
	 *
	 * @param       $data
	 * @param array $pathList
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	private static function resolveOptionValue ($data, array $pathList, $default) {

		foreach ( $pathList as $key ) {
			if ( isset($data[$key]) ) {
				$data = $data[$key];
			} else {
				return $default;
			}
		}

		return $data;
	}

	/**
	 * Set the option value and cache the result.
	 *
	 * @param string $path
	 * @param        $value
	 *
	 * @return bool
	 */
	public function set (string $path, $value) : bool {
		$pathList = explode('.', $path);
		$key      = $pathList[0];
		unset($pathList[0]);
		$data = $this->getData($key) ?? [];
		try {
			if ( empty($pathList) ) {
				if ( $value === 'unset' ) {
					$this->deleteData($key);

					return true;
				} else {
					$data = $value;
				}
			} else {
				$data = self::setValueInArray($data, $pathList, $value);
			}
			$this->setData($key, $data);

			return true;
		} catch ( \Throwable $e ) {
			return false;
		}
	}


	/**
	 * Set value in array structure.
	 *
	 * @param array $data
	 * @param array $pathList
	 * @param mixed $item
	 *
	 * @return array
	 */
	private static function setValueInArray (array $data, array $pathList, $item) : array {

		$index = array_shift($pathList);
		if ( empty($pathList) ) {
			if ( $item === 'unset' ) {
				unset($data[$index]);
			} else {
				$data[$index] = $item;
			}
		} else {

			if ( !isset($data[$index]) ) {
				$data[$index] = [];
			}
			$data[$index] = self::setValueInArray($data[$index], $pathList, $item);
		}

		return $data;
	}


}

class ToolInstallException extends \Exception {
}


class TerminalPHP {

	/* These commands are not executed */
	private array $config         = [];
	private array $helpsArgs      = ['-h', '--h', 'help', '--help'];
	private array $installContext = [];
	private array $packages       = [];

	/**
	 * initialize Class
	 *
	 * @param $path string default path to start
	 */
	public function __construct (string $path = '', $config = []) {
		if ( !empty($config) && is_array($config) ) {
			$this->config = $config;
		}
		$this->_cd($path);
		$this->putEnv();

	}

	/**
	 *
	 * @return array|false|null
	 */
	public function checkUpdate () {
		$cache          = Cache::getInstance();
		$lastCheckTime  = $cache->get('checkUpdate.lastTime');
		$lastUpdateInfo = $cache->get('checkUpdate.lastUpdateInfo');
		$isUpdateTime   = false;
		$now            = time();
		if ( isset($this->config['checkUpdate']) && $this->config['checkUpdate'] !== 'none' ) {
			$day                = 3600 * 24;
			$waitForCheckUpdate = $day;
			if ( $this->config['checkUpdate'] == 'week' ) {
				$waitForCheckUpdate = $day * 7;
			} else if ( $this->config['checkUpdate'] == 'month' ) {
				$waitForCheckUpdate = $day * 30;
			}
			if ( empty($lastCheckTime) || $now > ($lastCheckTime + $waitForCheckUpdate) ) {
				$isUpdateTime = true;
			}
		}

		$isUpdateCheckTime = $isUpdateTime && isset($this->config['debugMode']) && $this->config['debugMode'] === false;
		if ( $isUpdateCheckTime ) {
			$url  = "https://api.github.com/repos/javadamin1/terminal.php/releases/latest";
			$opts = [
				'http' => [
					'method'  => 'GET',
					'header'  => [
						'User-Agent: PHP-Terminal',
						'Accept: application/vnd.github.v3+json',
					],
					'timeout' => 5
				]
			];

			$context  = stream_context_create($opts);
			$response = @file_get_contents($url, false, $context);
			$cache->set('checkUpdate.lastTime', $now);

			if ( !$response ) {
				return null;
			}

			$data = json_decode($response, true);
			if ( !isset($data['tag_name']) ) {
				return null;
			}

			$latestVersion = ltrim($data['tag_name'], 'v');
			if ( version_compare($latestVersion, VERSION, '>') ) {
				$updateInfo = [
					'version'   => $latestVersion,
					'changelog' => $data['html_url'] ?? null,
					'body'      => $data['body'] ?? null,
				];
				$cache->set('checkUpdate.lastUpdateInfo', $updateInfo);

				return $updateInfo;
			}
		}

		if ( isset($lastUpdateInfo['version']) ) {
			if ( version_compare($lastUpdateInfo['version'], VERSION, '>') ) {
				return $lastUpdateInfo;
			}
		}

		return false;
	}

	/**
	 * Execute Shell Command
	 *
	 * @param $cmd string command
	 *
	 * @return string
	 */
	private function shell (string $cmd) : string {
		$resp = shell_exec($cmd . ' 2>&1 ');

		return !empty($resp) ? trim($resp) : '';
	}

	private function putEnv () : void {
		$originalPath = getenv('PATH');
		$binPath      = $this->config['tools']['binPath'] ?? '';
		if ( !empty($binPath) ) {
			putenv('PATH=' . $binPath . ':' . $originalPath);
		}

		$envPath = $this->config['envFile'] ?? '';
		if ( !file_exists($envPath) ) {
			return;
		}

		$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ( $lines as $line ) {
			$line = trim($line);
			if ( $line === '' || strpos($line, '#') === 0 ) {
				continue;
			}

			[$key, $val] = explode('=', $line, 2);
			$key = trim($key);
			$val = trim($val);
			if ( strpos($val, '~/') === 0 ) {
				$val = getenv('HOME') . substr($val, 1);
			}

			if ( !preg_match('~^(/|[A-Z]:)~i', $val) ) {
				$val = __DIR__ . '/' . $val;
			}
			putenv("$key=$val");
		}
	}

	/**
	 * Run Commands as Class method
	 *
	 * @param $cmd string command
	 * @param $arg array arguments
	 *
	 * @return string
	 */
	public function __call (string $cmd, array $arg) {
		return $this->runCommand($cmd . (isset($arg[0]) ? ' ' . $arg[0] : ''));
	}

	/**
	 * Run Command in Terminal
	 *
	 * @param $command string command to run
	 *
	 * @return string
	 */
	public function runCommand (string $command) : string {

		$args = explode(' ', $command);
		$cmd  = $args[0];
		unset($args[0]);
		$arg = count($args) > 0 ? implode(' ', $args) : '';
		// create local commend name
		$localCmd = '_' . $cmd;
		if ( method_exists($this, $localCmd) ) {
			return $this->$localCmd($arg);
		}
		if ( $this->isCommandBlocked($cmd) ) {
			return 'terminal.php: Permission denied';
		}

		if ( $this->isCommandAvailable($cmd) ) {
			$fullCmd = $cmd . ' ' . $arg;

			return $this->shell($fullCmd);
		}

		return 'terminal.php: command not found: ' . $cmd;
	}

	/**
	 * Normalize text for show in html
	 *
	 * @param $input string input text
	 *
	 * @return string
	 */
	public function normalizeHtml (string $input) : string {
		if ( empty($input) ) {
			return '';
		}

		return str_replace(['<', '>', "\n", "\t", ' '], [
			'&lt;',
			'&gt;',
			'<br>',
			'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
			'&nbsp;'
		], $input);
	}


	/**
	 * Array of Local Commands
	 *
	 * @return array
	 */
	private function getLocalCommands () : array {
		return array_filter(get_class_methods($this), static function ($methodName) {
			return strpos($methodName, '_') === 0 && strpos($methodName, '__') !== 0;
		});
	}

	/**
	 * @param $cmd string check commend exist
	 *
	 * @return bool
	 */
	public function isCommandAvailable (string $cmd) : bool {
		$cmd = escapeshellarg($cmd);
		if ( $this->shell('command -v ' . $cmd) ) {
			return true;
		}
		$output = shell_exec("which $cmd 2>/dev/null");

		return !empty($output) && !empty(trim($output));
	}

	/**
	 * @param $cmd string check comment is blocked in config
	 *
	 * @return bool
	 */
	private function isCommandBlocked (string $cmd) : bool {
		if ( !$this->config || !isset($this->config['blockedCommands']) ) {
			return false;
		}

		return is_array($this->config['blockedCommands']) && in_array($cmd, $this->config['blockedCommands']);
	}

	/**
	 * Array of All Commands
	 *
	 * @return array
	 */
	public function commandsList () : array {
		return array_merge(explode("\n", $this->ls('/usr/bin')), get_class_methods('CustomCommands'));
	}

	private function getAvailableCommandsFromCache () : array {
		if ( $this->config && isset($this->config['tools']) ) {

			$tools           = $this->config['tools'];
			$toolsCache      = $tools['cache'] ?? 'day';
			$cache           = Cache::getInstance();
			$lastCheckTime   = $cache->get('tools.lastCheckTime');
			$listAllCommands = $cache->get('tools.listAllCommands');
			$now             = time();
			$day             = 3600 * 24;
			$expireAt        = $day;
			$readFromCache   = false;
			if ( $toolsCache == 'week' ) {
				$expireAt = $day * 7;
			} else if ( $toolsCache == 'month' ) {
				$expireAt = $day * 30;
			}
			if ( !empty($lastCheckTime) && $now < ($lastCheckTime + $expireAt) ) {
				$readFromCache = true;
			}

			if ( $readFromCache && !empty($listAllCommands) ) {
				return $listAllCommands;
			}
			$newList = $this->listAllCommands();
			$data    = [
				'listAllCommands' => $newList,
				'lastCheckTime'   => $now
			];
			$cache->set('tools', $data);

			return $newList;
		}

		return $this->listAllCommands();
	}

	private function filterUsefulCommend ($term = '') : array {
		$useful = $this->config['tools']['useful'] ?? null;

		return array_filter($useful, function ($cmd) use ($term) {
			if ( empty($term) ) {
				return $this->isCommandAvailable($cmd);
			}

			return stripos($cmd, $term) !== false && $this->isCommandAvailable($cmd);
		});
	}

	public function searchAllCommands ($term) : string {
		$term     = strtolower($term);
		$commands = $this->getAvailableCommandsFromCache();
		$found    = array_filter($commands, static function ($cmd) use ($term) {
			return stripos($cmd, $term) !== false;
		});

		return empty($found) ? "No command found matching '$term'." : implode("\n", $found);
	}

	public function searchInUsefulCommands ($term) : string {
		$useful = $this->filterUsefulCommend($term);
		if ( !$useful ) {
			return $this->searchAllCommands($term);
		}

		return empty($useful) ? "No matching command found." : implode("\n", $useful);
	}

	/**
	 *  For create uniq array use array key
	 *
	 * @return array
	 */
	public function listAllCommands () : array {
		$paths    = explode(':', getenv('PATH'));
		$commands = [];
		foreach ( $paths as $dir ) {
			if ( !is_dir($dir) ) {
				continue;
			}
			foreach ( scandir($dir) as $file ) {
				$full = $dir . '/' . $file;
				if ( is_file($full) && is_executable($full) ) {
					$commands[$file] = true;
				}
			}
		}

		return array_keys($commands); // Unique command list
	}

	/**
	 * load repo data
	 */
	private function loadPackage () : void {
		$repoPath = $this->config['tools']['repoPath'] ?? '';
		if ( !file_exists($repoPath) ) {
			return;
		}
		try {
			$this->packages = json_decode(file_get_contents($repoPath), true, 512, JSON_THROW_ON_ERROR);
		} catch ( \Exception $e ) {
			$this->packages = [];
		}

	}

	private function installTool (string $toolName) : string {
		$this->loadPackage();
		if ( empty($this->packages) ) {
			return "😢 Package source file not loaded";
		}
		$packages = $this->packages;
		$explode  = explode('@', $toolName);
		$toolName = $explode[0];
		$version  = $explode[1] ?? '';


		if ( !isset($packages[$toolName]) ) {
			return "❌ Error: tool \"$toolName\" not found in repository.";
		}

		$tool = $packages[$toolName];

		if ( !empty($version) && !array_key_exists($version, $tool) ) {
			return "❌ Not found this version " . $version;
		}

		if ( !empty($version) ) {
			$installs = $tool[$version] ?? [];
		} else {
			$installs = $tool['default'] ?? [];
		}
		if ( empty($installs) ) {
			return "🤨 Not found install steps";
		}
		$steps  = $installs['install'];
		$output = "Installing {$toolName} (v{$installs['version']})...\n";

		try {
			foreach ( $steps as $step ) {
				$action = $step['action'] ?? 'unknown';
				$output .= "> $action \n";
				$result = $this->executeInstallStep($step);
				$output .= $result . "\n";
			}
		} catch ( ToolInstallException $e ) {
			$output .= $e->getMessage() . "\n❌ Installation aborted.";
		} catch ( \Throwable $e ) {
			$output .= "❌ Unexpected error: " . $e->getMessage() . "\n❌ Installation aborted.";
		}

		return $output;
	}

	/**
	 * @throws \ToolInstallException
	 * @throws \Exception
	 */
	private function executeInstallStep (array $step) : string {
		$action = $step['action'] ?? 'unknown';

		if ( $action === 'download' ) {
			$url  = $step['url'];
			$url  = $this->replacePlaceholders($url);
			$key  = $step['key'] ?? 'downloaded_file';
			$data = @file_get_contents($url);
			if ( !$data ) {
				throw new ToolInstallException("❌ Failed to download $url");
			}

			$to = $this->config['tools']['downloadPath'] ?? '/tools';
			if ( !is_dir($to) && !mkdir($to, 0755, true) && !is_dir($to) ) {
				throw new \ToolInstallException(sprintf('Directory "%s" was not created', $to));
			}

			$filename = basename(parse_url($url, PHP_URL_PATH));
			$filePath = rtrim($to, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

			file_put_contents($filePath, $data);

			if ( !file_exists($filePath) ) {
				throw new ToolInstallException("❌ Failed to download $url");
			}
			$this->installContext[$key] = $filePath;

			return "✅ Downloaded to $filePath";
		}

		if ( $action === 'delete' ) {

			$target = $this->replacePlaceholders($step['target']);
			if ( !file_exists($target) ) {
				return "Target not found: $target";
			}

			if ( is_file($target) ) {
				if ( @unlink($target) ) {
					return "Deleted file: $target";
				}

				return "Failed to delete file: $target";
			}

			if ( is_dir($target) ) {
				$this->shell("rm -rf " . escapeshellarg($target));

				return file_exists($target) ? "Failed to delete directory: $target" : "Deleted directory: $target";
			}

			return "Unknown target type: $target";
		}

		if ( $action === 'extract' ) {
			$file     = $this->replacePlaceholders($step['file'] ?? '');
			$dest     = $this->replacePlaceholders($step['path'] ?? '');
			$dest     = Helper::fixDir($this->config['tools']['binPath'] ?? '') . ltrim($dest, '/');
			$key      = $step['key'] ?? '';
			$excludes = $step['excludes'] ?? [];

			return $this->extract($file, $dest, $key, $excludes);
		}

		if ( $action === 'shell' ) {
			$cmd    = $this->replacePlaceholders($step['cmd']);
			$result = $this->shell($cmd);

			return "✅ Ran shell: $cmd\n >" . trim($result);
		}

		if ( $action === 'symlink' ) {
			$src  = $this->replacePlaceholders($step['target']);
			$link = $this->replacePlaceholders($step['link']);

			if ( !$link || !$src || !file_exists($src) ) {
				throw new ToolInstallException("❌ Symlink error: missing source or link : target => $src ,link => $link");
			}
			$bin  = $this->config['tools']['binPath'];
			$dest = Helper::fixDir($bin) . basename($link);
			@unlink($dest);
			$result = @symlink($src, $dest);
			if ( $result === false ) {
				throw new ToolInstallException("❌ Symlink error: can't link   $src to $link");
			}
			@chmod($dest, 0755);

			return "✅ Symlink created: $dest → $src";
		}
		if ( $action === 'mv' ) {
			$src = $this->replacePlaceholders($step['target']);
			if ( !file_exists($src) ) {
				throw new ToolInstallException("❌ Source file not found for mv: $src");
			}
			$path = $this->replacePlaceholders($step['path']);
			if ( !$path ) {
				throw new ToolInstallException("❌ mv error: path not defined");
			}

			$bin = $this->config['tools']['binPath'];
            Helper::mkdir($bin);

			$filename = basename($src);
			$dest     = Helper::fixDir($bin) . $filename;
			rename($src, $dest);
			$key                        = $step['key'] ?? 'movePath';
			$this->installContext[$key] = $dest;

			return "✅ move file: $src → $dest";
		}
		if ( $action === 'run' ) {

			$fn      = $this->replacePlaceholders($step['fn']);
			$value   = $this->replacePlaceholders($step['value'] ?? '');
			$localFn = '_' . $fn;
			$key     = $step['key'] ?? 'functionData';
			if ( method_exists($this, $fn) ) {
				throw new ToolInstallException("❌ Function  not found in terminal: $fn");
			}

			try {
				$result                     = $this->{$localFn}($value);
				$this->installContext[$key] = $result;

				return "✅ run $fn successfully ";

			} catch ( Throwable $e ) {
				throw new ToolInstallException("❌ Function get exception: $fn");
			}
		}
		if ( $action === 'sync' ) {
			$zipPath  = $this->replacePlaceholders($step['file'] ?? '');
			$target   = $this->replacePlaceholders($step['target'] ?? '');
			$excludes = $step['excludes'] ?? [];

			$key = $step['key'] ?? '';

			$this->syncZip($zipPath, $target, $key, $excludes);
		}

		throw new ToolInstallException("❓ Unknown action: $action");
	}

	/**
	 * @param        $file
	 * @param        $dest
	 * @param string $key
	 * @param array  $exclude
	 *
	 * @return string
	 * @throws \RuntimeException|\ToolInstallException
	 */
	public function extract ($file, $dest, string $key = '', array $exclude = []) : string {

		if ( !$file || !file_exists($file) ) {
			throw new  ToolInstallException("❌ Extract error: File not found or not specified: " . htmlspecialchars($file));
		}

		Helper::mkdir($dest);

		$ext = strtolower($file);
		if ( substr($ext, -4) === '.zip' ) {
			if ( !class_exists('ZipArchive') && !$this->shell('command -v unzip') ) {
				throw new ToolInstallException("⛔ Neither ZipArchive nor shell unzip is available.");
			}

			if ( !class_exists('ZipArchive') ) {
				$cmd  = sprintf('unzip -q %s -d %s', escapeshellarg($file), escapeshellarg($dest));
				$resp = $this->shell($cmd);
				if ( !$resp ) {
					throw new ToolInstallException("❌ Extract error: PHP ZipArchive not available and failed to extract zip using shell command.");
				}

				return '✅ Extracted successfully to: ' . $resp;
			}

			$zip = new ZipArchive();
			$res = $zip->open($file);
			if ( $res === true ) {
				if ( !$zip->extractTo($dest) ) {
					throw new ToolInstallException("❌ Extract error: Failed to extract ZIP to: " . htmlspecialchars($dest));
				}
				$zip->close();
			} else {
				throw new ToolInstallException("❌ Extract error: Cannot open ZIP file. Error code: $res");
			}

		} elseif ( preg_match('/\.tar\.gz$|\.tgz$/', $ext) ) {
			$cmd    = "tar -xzf " . escapeshellarg($file) . " -C " . escapeshellarg($dest) . " 2>&1";
			$output = $this->shell($cmd);
			if ( !is_dir($dest) || !scandir($dest) ) {
				throw new ToolInstallException("❌ Extract error: Failed to extract .tar.gz file.\nOutput:\n$output");
			}

		} elseif ( preg_match('/\.tar\.xz$/', $ext) ) {
			$cmd    = "tar -xJf " . escapeshellarg($file) . " -C " . escapeshellarg($dest) . " 2>&1";
			$output = $this->shell($cmd);
			if ( !is_dir($dest) || !scandir($dest) ) {
				return "❌ Extract error: Failed to extract .tar.xz file.\nOutput:\n$output";
			}

		} elseif ( preg_match('/\.tar$/', $ext) ) {
			$cmd    = "tar -xf " . escapeshellarg($file) . " -C " . escapeshellarg($dest) . " 2>&1";
			$output = $this->shell($cmd);
			if ( !is_dir($dest) || !scandir($dest) ) {
				throw new ToolInstallException("❌ Extract error: Failed to extract .tar file.\nOutput:\n$output");
			}

		} else {
			throw new ToolInstallException("❌ Extract error: Unsupported file type: " . htmlspecialchars($ext));
		}

		if ( $key ) {
			$this->installContext[$key] = $dest;
		}

		return "✅ Extracted successfully to: " . htmlspecialchars($dest);
	}

	/**
	 * @throws \ToolInstallException
	 */
	public function syncZip ($zipPath, $targetDir, $key = '', $excludes = []) : void {
		$tmp = sys_get_temp_dir() . '/terminal_upgrade_' . uniqid('', true);
		Helper::mkdir($tmp);
		$key  = $key ?: 'syncExtract';
		$html = $this->extract($zipPath, $targetDir, $key, $excludes);


		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
		Helper::dd($html, $tmp, $iterator);
		foreach ( $iterator as $item ) {
			$relPath  = substr($item->getPathname(), strlen($tmp) + 1);
			$destPath = $targetDir . '/' . $relPath;

			$isExcluded = false;
			foreach ( $excludes as $ex ) {
				if ( strpos($relPath, $ex) === 0 ) {
					$isExcluded = true;
					break;
				}
			}
			if ( $isExcluded ) {
				continue;
			}

			if ( $item->isDir() ) {
				if ( !is_dir($destPath) ) {
					mkdir($destPath, 0755, true);
				}
			} else {
				copy($item->getPathname(), $destPath);
			}
		}

		// حذف فایل‌های قدیمی که در نسخه جدید نیستند
		$existingFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

		foreach ( $existingFiles as $item ) {
			$relPath = substr($item->getPathname(), strlen($targetDir) + 1);
			$newPath = $tmp . '/' . $relPath;

			$isExcluded = false;
			foreach ( $excludes as $ex ) {
				if ( strpos($relPath, $ex) === 0 ) {
					$isExcluded = true;
					break;
				}
			}
			if ( $isExcluded ) {
				continue;
			}

			if ( !file_exists($newPath) ) {
				if ( $item->isDir() ) {
					@rmdir($item->getPathname());
				} else {
					@unlink($item->getPathname());
				}
			}
		}

		// پاکسازی
		@unlink($zipPath);
		shell_exec('rm -rf ' . escapeshellarg($tmp));
	}


	private function replacePlaceholders (string $str) : string {
		foreach ( $this->installContext as $key => $value ) {
			$str = str_replace('{' . $key . '}', $value, $str);
		}

		return $str;
	}

	protected function upgradeSelf () : string {
		$updateInfo = $this->checkUpdate();
		if ( !$updateInfo ) {
			return "Your terminal is already up-to-date.";
		}

		$url = $updateInfo['changelog'] ?? null;
		if ( !$url ) {
			return "Update URL not found.";
		}

		$downloadUrl = str_replace('/tag/', '/download/', $url) . '/terminal.phar';
		$html        = "Downloading version {$updateInfo['version']}..." . PHP_EOL;

		$file = file_get_contents($downloadUrl);
		if ( !$file ) {
			$html .= "Failed to download the new version.";

			return $html;
		}

		file_put_contents(__FILE__, $file);

		return "Updated to version {$updateInfo['version']}. Please restart.";
	}

	public function upgradeTool (string $args, string $threeWord) : string {

		if ( empty($args) ) {
			return "No tool specified. Use `tools upgrade self` or `tools upgrade <tool>`.";
		}

		if ( $args === 'self' ) {
            if (empty($threeWord) || $threeWord !== '-y') {
	            return "⚠️ Upgrading 'self' will sync the terminal directory, remove unused files, and add new files. If you have custom files, the upgrade may overwrite them. Cancel the upgrade or confirm by adding '-y'. ⚠️";
            }
			return $this->installTool('self');
		}

		Helper::dd('not Self', $args);
		$toolParts = explode('@', $args[0]);
		$tool      = $toolParts[0];
		$version   = $toolParts[1] ?? null;

		return $this->upgradeTool($tool, $version);
	}




	/************************************************************/
	/*                      Local Commands                      */
	/*                                                          */
	/*             note: command must start with '_'            */
	/************************************************************/

	/**
	 * Change Directory Command
	 *
	 * @param $path string patch to change
	 *
	 * @return void
	 */
	private function _cd ($path) {
		if ( $path ) {
			chdir($path);
		}

	}

	/**
	 * Current Working Directory Command
	 *
	 * @return string
	 */
	private function _pwd () {
		return getcwd();
	}

	/**
	 * Ping Command
	 *
	 * @return string
	 */
	private function _ping ($a) {

		if ( strpos($a, '-c ') !== false ) {
			return $this->shell('ping ' . $a);
		}

		return $this->shell('ping -c 4 ' . $a);
	}

	public function _tools ($arg) : string {

		$arg       = trim($arg);
		$explode   = explode(' ', $arg);
		$cmd       = $explode[0] ?? '';
		$arg       = $explode[1] ?? '';
		$threeWord = $explode[2] ?? '';

		if ( in_array($cmd, $this->helpsArgs, true) ) {
			return <<<HELP
                            Usage: tools <command> [args]
                            
                            Available commands:
                            
                              tools ls                     List common useful developer tools available on this system
                              tools la                     List all available system commands (cached)
                              tools search <term>          Search among currently installed tools (local list)
                              tools search -r <term>       Search in remote list of installable tools
                              tools search -a <term>       Search all system commands available in PATH
                              tools install <tool>         Install the default version of a tool (e.g., tools install node)
                              tools install <tool>@<ver>   Install a specific version of a tool (e.g., tools install node@20.10.0)
                              tools help                   Show this help message
                            
                            Note:
                              - "ls" and "search" query locally installed tools.
                              - "search -r" queries the remote list and shows installable tools and their versions.
                              - "search -a" searches all commands available in your system's PATH.
                              - Use "@<version>" with "install" to install a specific version.
                              - Multi-version tools (like node, composer, git) are supported.
                            HELP;

		}

		if ( in_array($cmd, ['ls', 'list', 'la']) ) {
			$commends = $this->getAvailableCommandsFromCache();
			if ( $cmd !== 'la' ) {
				$filtered = $this->filterUsefulCommend();
				if ( !empty($filtered) ) {
					$commends = $filtered;
				}
			}

			return implode("\n", $commends);
		}

		if ( ($cmd === 'search') ) {
			$searchTerm = trim($arg);

			if ( empty($searchTerm) ) {
				return 'usage: app search <keyword>';
			}
			if ( $arg === '-a' ) {
				return $this->searchAllCommands($threeWord);
			}

			if ( $arg === '-r' ) {
				$this->loadPackage();
				if ( !$this->packages ) {
					return "😢 Package source file not loaded";
				}
				$exist     = [];
				$available = array_keys($this->packages);
				foreach ( $available as $package ) {
					if ( strpos($package, $threeWord) !== false ) {
						$exist[] = $package;
					}
				}
				if ( empty($exist) ) {
					return '😩 Package "' . $threeWord . '" not found';;
				}
				$resp = '';
				foreach ( $exist as $value ) {
					$data = $this->packages[$value];
					if ( !is_array($data) ) {
						continue;
					}
					$defaultVersion = $data['default']['version'] ?? '';
					unset($data['default']);
					$versions = array_keys($data);
					if ( !empty($defaultVersion) ) {
						$versions[] = $defaultVersion;
					}

					$resp .= 'Package "' . $value . '" found. available versions: [' . implode(', ', $versions) . ' ] ' . PHP_EOL . '  ';
				}

				return $resp;
			}

			return $this->searchInUsefulCommands($searchTerm);

		}

		if ( $cmd === 'install' ) {
			return $this->installTool($arg);
		}
		if ( $cmd === 'upgrade' ) {
			return $this->upgradeTool($arg, $threeWord);
		}

		return 'terminal.php: Not found commend';
	}

	/**
	 * @return string host os name
	 */
	public static function _os () : string {
		return Helper::detectOS();
	}


}

$laravelMode = isset($config['laravelMode']) && $config['laravelMode'];

/* Check if Request is Ajax */
if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_POST['command']) ) {

	$command   = explode(' ', $_REQUEST['command'])[0];
	$arguments = array_slice(explode(' ', $_REQUEST['command']), 1);
	$path      = isset($_REQUEST['path']) ? $_REQUEST['path'] : '';
	$terminal  = new TerminalPHP($path, $config ?? []);
	$result    = '';
	if ( KEY === 'YourRandomSecureKey' && isset($config['debugMode']) && $config['debugMode'] === false ) {
		$resp = json_encode([
			'result' => 'Terminal access denied. You are using the default KEY. Please edit terminal.php and set a secure, custom KEY to enable access.',
			'path'   => $terminal->pwd()
		]);
		exit($resp);
	}
	if ( in_array($command, get_class_methods('CustomCommands')) ) {
		$result = CustomCommands::$command($arguments);
	} else {
		$command = $terminal->runCommand($_REQUEST['command']);
		if ( is_string($command) ) {
			$result = $terminal->normalizeHtml($command);
		}
	}
	$resp = json_encode([
		'result' => $result,
		'path'   => $terminal->pwd()
	]);

	exit($resp);
}

$terminal = new TerminalPHP('', $config ?? []);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <link rel="icon" type="image/x-icon" href="./favicon.png">
    <meta charset="utf-8">
    <title>Terminal.php</title>

    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css"/>
	<?php
	if ( $laravelMode ) {
		?>
        <meta name="csrf-token" content="<?= function_exists('csrf_token') ? csrf_token() : '' ?>">
		<?php
	}
	?>

    <style>
        :root {
            --background-url: url('http://files.javadfathi.ir/terminal-background.jpeg');
            --font: Vazirmatn, sans-serif;
            --font-size: 14px;
            --primary-color: #101010;
            --color-scheme-1: #55c2f9;
            --color-scheme-2: #ff5c57;
            --color-scheme-3: #5af68d;
            --scrollbar-color: #181818;
            --title-color: white;
            --blink-color: #979797;
            --blink: '|';
            --separator: '--->';
        }

        ::-webkit-scrollbar {
            width: 7px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--scrollbar-color);
            border-radius: 5px;
        }

        * {
            font-family: var(--font);
        }

        body {
            background: var(--background-url) center no-repeat;
            background-size: cover;
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            background-attachment: fixed;
            overflow: hidden;
        }

        a {
            color: #29a9ff;
        }

        #header {
            height: 5em;
        }

        #header .update {
            background: #699f22;
            padding: 1em;
            border: 1px solid #207604;
            color: #000000;
            margin: 1em;
            width: max-content;
            border-radius: 15px;
            max-height: 2em;
        }

        #header .update a {
            font-weight: bold;
            color: #f5f1f1;
        }

        #terminal {
            display: flex;
            justify-content: center;
            height: 80vh;
            width: 100%;
        }

        terminal {
            display: block;
            width: 80vw;
            height: 100%;
            position: relative;
            background: inherit;
            border-radius: 10px;
            max-width: 70rem;
            overflow: hidden;
        }

        terminal::before,
        terminal::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
            border-radius: 10px;
        }

        terminal::before {
            background: inherit;
            filter: blur(.5rem);
        }

        terminal::after {
            background: var(--primary-color);
            opacity: .75;
        }

        terminal header {
            position: absolute;
            width: 100%;
            height: 45px;
            background: var(--primary-color);
            z-index: 1;
            border-radius: 10px 10px 0 0;
            user-select: none;
        }

        terminal header .terminal-title {
            display: block;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            text-align: center;
            color: var(--title-color);
            line-height: 45px;
            opacity: .8;
            z-index: -1;
        }

        terminal header .buttons {
            padding: 1rem;
            display: block;
        }

        terminal header .buttons * {
            display: inline-block;
            width: 15px;
            height: 15px;
            background: rgba(255, 255, 255, .1);
            border-radius: 50%;
            margin-right: 5px;
            cursor: pointer;
        }

        terminal header .buttons .close {
            background: #fc615d;
        }

        terminal header .buttons .maximize {
            background: #fdbc40;
        }

        terminal header .buttons .minimize {
            background: #34c749;
        }

        terminal .content {
            position: absolute;
            left: 1.5%;
            top: 60px;
            width: 98%;
            height: 85%;
            z-index: 1;
            overflow-x: hidden;
            overflow-y: auto;
            color: #ececec;
            font-size: var(--font-size);
            box-sizing: border-box;
        }

        terminal .content line {
            display: block;
            width: 98% !important;
            overflow-wrap: break-word;
            white-space: normal;
        }

        terminal .content path {
            color: var(--color-scheme-1);
        }

        terminal .content sp {
            color: var(--color-scheme-2);
            letter-spacing: -6px;
            margin-right: 5px;
        }

        terminal .content sp::before {
            content: var(--separator);
        }

        terminal .content cm {
            color: var(--color-scheme-3);
        }

        terminal .content code {
            display: inline;
            margin: 0;
            white-space: unset;
        }

        terminal .content bl {
            color: var(--blink-color);
            position: relative;
            top: -2px;
        }

        terminal .content bl::before {
            content: var(--blink);
            animation: blink 2s steps(1) infinite;
        }

        footer {
            position: absolute;
            width: 100%;
            left: 0;
            bottom: 20px;
            color: white;
            text-align: center;
            font-size: 12px;
        }

        footer a {
            text-decoration: none;
            color: #fdbc40;
        }

        @keyframes blink {
            0% {
                opacity: 1
            }
            50% {
                opacity: 0
            }
            100% {
                opacity: 1
            }
        }

        #loader-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 110%;
            height: 110%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .show-loader {
            display: flex !important;
        }

        .loader {
            width: 48px;
            height: 48px;
            display: inline-block;
            position: relative;
            z-index: 10;
        }

        .loader::after,
        .loader::before {
            content: '';
            box-sizing: border-box;
            width: 48px;
            height: 48px;
            border: 2px solid #FFF;
            position: absolute;
            left: 0;
            top: 0;
            animation: rotation 2s ease-in-out infinite alternate;
        }

        .loader::after {
            border-color: #FF3D00;
            animation-direction: alternate-reverse;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

    </style>
</head>
<body>
<div id="header">
	<?php
	$update = $terminal->checkUpdate();
	if ( $update ) {
		$currentVersion = VERSION;
		echo "<div class='update'>⚠️ A new version <strong>{$update['version']}</strong> is available. You are using <strong>{$currentVersion}</strong>. 
    <a href='https://github.com/javadamin1/terminal.php/releases' target='_blank'>Update now</a></div>";
	}
	?>
</div>
<div id="terminal">
    <terminal>

        <header>
            <div class="buttons">
                <span class="close" title="close"></span>
                <span class="maximize" title="maximize"></span>
                <span class="minimize" title="minimize"></span>
            </div>
            <div class="terminal-title">Terminal.php
                &nbsp; <?= '(' . ($terminal->whoami() ? $terminal->whoami() : '') . ($terminal->whoami() && $terminal->hostname() ? '@' . $terminal->hostname() : '') . ')'; ?>
            </div>
        </header>
        <div id="loader-overlay">
            <span class="loader"></span>
        </div>
        <div class="content">
            <line class="current">
                <path><?= $terminal->pwd(); ?></path>
                <sp></sp>
                <t>
                    <bl></bl>
                </t>
            </line>
        </div>

    </terminal>
</div>


<footer>Coded by <a href="https://github.com/smartwf">SmartWF</a>And modified BY<a href="https://github.com/javadamin1">javad
        fathi</a></footer>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script type="text/javascript">
    let commands_list = <?php print_r(json_encode($terminal->commandsList())); ?>;

    function showLoader() {
        $('#loader-overlay').addClass('show-loader')
    }

    function hideLoader() {
        $('#loader-overlay').removeClass('show-loader')
    }

    function isLoaderShowing() {
        return $('#loader-overlay').hasClass('show-loader')
    }

</script>

<script type="text/javascript">
    var path                        = '<?= $terminal->pwd()?>';
    var command                     = '';
    var command_history             = [];
    var history_index               = 0;
    var suggest                     = false;
    var blink_position              = 0;
    var autocomplete_position       = 0;
    var autocomplete_search_for     = '';
    var autocomplete_temp_results   = [];
    var autocomplete_current_result = '';
    let laravelMode                 = <?= $laravelMode ? 'true' : 'false' ?>;

    $(document).bind('paste', function (e) {
        let data = e.originalEvent.clipboardData.getData('Text');
        type(data)
        $('terminal .content').scrollTop($('terminal .content').prop("scrollHeight"));
    })

    $(document).keydown(async function (e) {
        if (isLoaderShowing()) {
            return;
        }
        var keyCode = typeof e.which === "number" ? e.which : e.keyCode;

        /* Tab, Backspace and Delete key */
        if (keyCode === 8 || keyCode === 9 || keyCode === 46) {
            e.preventDefault();
            if (command !== '') {
                if (keyCode === 8)
                    backSpace();
                else if (keyCode === 46)
                    reverseBackSpace();
                else if (keyCode === 9)
                    autoComplete();
            }
        }

        /* Ctrl + C */
        else if (e.ctrlKey && keyCode === 67) {
            autocomplete_position = 0;
            endLine();
            newLine();
            reset();
        }
        /* Ctrl + V */
        else if ((e.ctrlKey && keyCode === 86)) {

        }
        /* Enter */
        else if (keyCode === 13) {
            if (autocomplete_position !== 0) {
                autocomplete_position = 0;
                command               = autocomplete_current_result;
            }

            if (command.toLowerCase().split(' ')[0] in commands) {
                commands[command.toLowerCase().split(' ')[0]](command.split(' ').slice(1));
            } else if (command.length !== 0) {

                showLoader()
                await $.ajax({
                    type    : 'POST',
                    headers : {
                        'X-CSRF-TOKEN': laravelMode ? $('meta[name="csrf-token"]').attr('content') : ''
                    },
                    data    : {command: command, path: path},
                    cache   : false,
                    dataType: 'json',
                    success : function (response) {
                        path = response.path;
                        $('terminal .content').append('<line>' + response.result + '</line>');
                        hideLoader()
                    },
                    error   : function () {
                        hideLoader()
                    }
                });
            }


            endLine();
            addToHistory(command);
            newLine();
            reset();
            $('terminal .content').scrollTop($('terminal .content').prop("scrollHeight"));
        }

        /* Home, End, Left and Right (change blink position) */
        else if ((keyCode === 35 || keyCode === 36 || keyCode === 37 || keyCode === 39) && command !== '') {
            e.preventDefault();
            $('line.current bl').remove();

            if (autocomplete_position !== 0) {
                autocomplete_position = 0;
                command               = autocomplete_current_result;
            }

            if (keyCode === 35)
                blink_position = 0;

            if (keyCode === 36)
                blink_position = command.length * -1;

            if (keyCode === 37 && command.length !== Math.abs(blink_position))
                blink_position--;

            if (keyCode === 39 && blink_position !== 0)
                blink_position++;

            printCommand();
            normalizeHtml();
        }

        /* Up and Down (suggest command from history)*/
        else if ((keyCode === 38 || keyCode === 40) && (command === '' || suggest)) {
            e.preventDefault();
            if (keyCode === 38
                && command_history.length
                && command_history.length >= history_index * -1 + 1) {

                history_index--;
                command = command_history[command_history.length + history_index];
                printCommand();
                normalizeHtml();
                suggest = true;
            } else if (keyCode === 40
                && command_history.length
                && command_history.length >= history_index * -1
                && history_index !== 0) {

                history_index++;
                command = (history_index === 0) ? '' : command_history[command_history.length + history_index];
                printCommand();
                normalizeHtml();
                suggest = (history_index === 0) ? false : true;
            }
        }

        /* type characters */
        else if (keyCode === 32
            || keyCode === 222
            || keyCode === 220
            || (
                (keyCode >= 45 && keyCode <= 195)
                && !(keyCode >= 112 && keyCode <= 123)
                && keyCode != 46
                && keyCode != 91
                && keyCode != 93
                && keyCode != 144
                && keyCode != 145
                && keyCode != 45
            )
        ) {
            type(e.key);
            $('terminal .content').scrollTop($('terminal .content').prop("scrollHeight"));
        }
    });

    function reset() {
        command                     = '';
        history_index               = 0;
        blink_position              = 0;
        autocomplete_position       = 0;
        autocomplete_current_result = '';
        suggest                     = false;
    }

    function endLine() {
        $('line.current bl').remove();
        $('line.current').removeClass('current');
    }

    function newLine() {
        $('terminal .content').append('<line class="current"><path>' + path + '</path> <sp></sp> <t><bl></bl></t></line>');
    }

    function addToHistory(command) {
        if (command.length >= 2 && (command_history.length === 0 || command_history[command_history.length - 1] !== command))
            command_history[command_history.length] = command;
    }

    function normalizeHtml() {
        let res  = $('line.current t').html();
        let nres = res.split(' ').length == 1 ? '<cm>' + res + '</cm>' : '<cm>' + res.split(' ')[0] + '</cm> <code>' + res.split(' ').slice(1).join(' ').replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</code>';

        $('line.current t').html(nres.replace('&lt;bl&gt;&lt;/bl&gt;', '<bl></bl>'));
    }

    function printCommand(cmd = '') {
        if (cmd === '')
            cmd = command;
        else
            blink_position = 0;

        let part1 = cmd.substr(0, cmd.length + blink_position);
        let part2 = cmd.substr(cmd.length + blink_position);

        $('line.current t').html(part1 + '<bl></bl>' + part2);
    }

    function type(t) {
        history_index = 0;
        suggest       = false;

        if (autocomplete_position !== 0) {
            autocomplete_position = 0;
            command               = autocomplete_current_result;
        }
        if (command[command.length - 1] === '/' && t === '/')
            return;

        let part1 = command.substr(0, command.length + blink_position);
        let part2 = command.substr(command.length + blink_position);
        command   = part1 + t + part2;

        printCommand();
        normalizeHtml();
    }

    function backSpace() {
        if (autocomplete_position !== 0) {
            autocomplete_position = 0;
            command               = autocomplete_current_result;
        }

        let part1 = command.substr(0, command.length + blink_position);
        let part2 = command.substr(command.length + blink_position);
        command   = part1.substr(0, part1.length - 1) + part2;

        printCommand();
        normalizeHtml();
    }

    function reverseBackSpace() {
        let part1 = command.substr(0, command.length + blink_position);
        let part2 = command.substr(command.length + blink_position);
        command   = part1 + part2.substr(1);

        if (blink_position !== 0)
            blink_position++;

        printCommand();
        normalizeHtml();
    }

    async function autoComplete() {
        if (autocomplete_search_for !== command) {
            autocomplete_search_for   = command;
            autocomplete_temp_results = [];

            let parts         = command.split(' ');
            let cmd           = parts[0];
            let cmd_parameter = parts[1] || '';

            if (parts.length === 1) {
                let executableList        = commands_list.concat(Object.keys(commands));
                autocomplete_temp_results = executableList.filter(function (cm) {
                    return (cm.length > command.length && cm.substr(0, command.length).toLowerCase() == command.toLowerCase());
                })
                    .reverse().sort(function (a, b) {
                        return b.length - a.length;
                    });
            } else if (parts.length === 2) {
                var temp_cmd = '';

                if (cmd === 'cd' || cmd === 'cp' || cmd === 'mv' || cmd === 'cat' || cmd === 'rm') {
                    switch (cmd) {
                        case 'rm':
                        case "cd":
                        case "cp":
                        case "mv":
                            temp_cmd = 'ls -d ' + cmd_parameter + '*/';
                            break;
                        case "cat":
                            temp_cmd = 'ls -p | grep -v /';
                            break;
                        default:
                            temp_cmd = '';
                    }

                    await $.ajax({
                        type    : 'POST',
                        headers : {
                            'X-CSRF-TOKEN': laravelMode ? $('meta[name="csrf-token"]').attr('content') : ''
                        },
                        data    : {command: temp_cmd, path: path},
                        cache   : false,
                        dataType: 'json',
                        success : function (response) {
                            autocomplete_temp_results = response.result.split('<br>')
                                .filter(function (cm) {
                                    return (cm.length !== 0);
                                });
                        }
                    });
                }
            }
        }

        if (autocomplete_temp_results.length && autocomplete_temp_results.length > Math.abs(autocomplete_position)) {
            autocomplete_position--;
            autocomplete_current_result = ((command.split(' ').length === 2) ? command.split(' ')[0] + ' ' : '') + autocomplete_temp_results[autocomplete_temp_results.length + autocomplete_position];
            printCommand(autocomplete_current_result);
            normalizeHtml();
        } else {
            autocomplete_position       = 0;
            autocomplete_current_result = '';
            printCommand();
            normalizeHtml();
        }
    }


    /**********************************************************/
    /*                     Local Commands                     */
    /**********************************************************/

    var commands = {
        'clear'  : clear,
        'history': history
    };

    function clear() {
        $('terminal .content').html('');
    }

    function history(arg) {
        var res        = [];
        let start_from = arg.length ? Number.isInteger(Number(arg[0])) ? Number(arg[0]) : 0 : 0;

        if (start_from != 0 && start_from <= command_history.length)
            for (var i = command_history.length - start_from; i < command_history.length; i++) {
                res[res.length] = (i + 1) + ' &nbsp;' + command_history[i];
            }
        else
            command_history.forEach(function (item, index) {
                res[res.length] = (index + 1) + ' &nbsp;' + item;
            });

        $('terminal .content').append('<line>' + res.join('<br>') + '</line>');
    }

</script>
</body>

</html>
