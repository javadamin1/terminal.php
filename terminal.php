<?php

// For this 'fatal: $HOME not set' error set Home
#putenv("HOME=/tmp");
#putenv('COMPOSER_HOME=/home/yourDomainName');

/**
 * Terminal.php - Terminal Emulator for PHP
 *
 * @package  Terminal.php
 * @author   SmartWF <hi@smartwf.ir>
 */

/* Choose a random key Like ('Mmbuge8maD5VAUMc') for Security */
const KEY = 'Mmbuge8maD5VAUMc';
$isset = isset($_GET['key']);
$key   = $_GET['key'] ?? null;
if ( KEY != '' && !$isset && $key != KEY ) {
	header('location: /');
}

if ( !function_exists('shell_exec') ) {
	die('Sorry, this server has blocked shell access :(');
}

/**
 *  config
 */
$config = [
	'tools'           => [
		'cacheFile' => __DIR__ . '/cache/commands.json',
		'cache'     => true,    // bool active or not active
		'expireAt'  => 'never', // OR integer by hour like 24 for 1 day,
		'useful'    => [ // tools list for search in install tools
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
		]
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
	]
];

class CustomCommands {

	/***************************************************************
	 *                 Add Your Custom Command Here                *
	 ***************************************************************
	 *    note 1: Function Name is Command and return is Result    *
	 *    note 2: $a is array of arguments                         *
	 * *************************************************************/

	public static function hi ($a) {
		return 'Hi ' . implode(' ', $a);
	}

	public static function md5 ($a) {
		$input = implode(' ', $a);
		if ( $input ) {
			return md5($input);
		} else {
			return 'write something, example:<br>md5 test';
		}
	}

	public static function developer () {
		return 'SmartWF<br><a href="https://github.com/smartwf" target="_blank">github</a> &nbsp; &nbsp; <a href="mailto:hi@smartwf.ir" target="_blank">mail</a> &nbsp; &nbsp; <a href="http://twitter.com/smartwf" target="_blank">twitter</a>';
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

}

class TerminalPHP {

	/* These commands are not executed */
	private array $config = [];

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
	}

	/**
	 * Execute Shell Command
	 *
	 * @param $cmd string command
	 *
	 * @return string
	 */
	private function shell ($cmd) {
		$resp = shell_exec($cmd . ' 2>&1 ');

		return $resp ? trim($resp) : $resp;
	}


	/**
	 * Run Commands as Class method
	 *
	 * @param $cmd string command
	 * @param $arg array arguments
	 *
	 * @return string
	 */
	public function __call ($cmd, $arg) {
		return $this->runCommand($cmd . (isset($arg[0]) ? ' ' . $arg[0] : ''));
	}

	/**
	 * Run Command in Terminal
	 *
	 * @param $command string command to run
	 *
	 * @return string
	 */
	public function runCommand (string $command) {

		$args = explode(' ', $command);
		$cmd  = $args[0];
		unset($args[0]);
		// For wildcard
		$allowedForWildcard = ['ls', 'cat'];

		$escapedArgs = array_map(function ($value) use ($cmd, $allowedForWildcard) {
			if ( in_array($cmd, $allowedForWildcard) && preg_match('/^[\w\-\.\*\/]+$/', $value) ) {
				return $value;
			}

			return escapeshellarg($value);
		}, $args);
		$arg         = count($args) > 0 ? implode(' ', $args) : '';
		// create local commend name
		$localCmd = '_' . $cmd;
		if ( in_array($localCmd, $this->getLocalCommands()) ) {
			return $this->$localCmd($arg);
		} else if ( $this->isCommandBlocked($cmd) ) {
			return 'terminal.php: Permission denied';
		} else if ( $this->isCommandAvailable($cmd) ) {
			$fullCmd = $cmd . ' ' . implode(' ', $escapedArgs);

			return $this->shell($fullCmd);
		} else {
			return 'terminal.php: command not found: ' . $cmd;
		}
	}

	/**
	 * Normalize text for show in html
	 *
	 * @param $input string input text
	 *
	 * @return string
	 */
	public function normalizeHtml (string $input) : string {
		if ( empty($input)) {
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
		return array_filter(get_class_methods($this), function ($methodName) {
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

	private function getAvailableCommandsFromCache () : array {
		if ( $this->config && isset($this->config['tools']) ) {
			$tools         = $this->config['tools'];
			$isActiveCache = $tools['cache'] ?? false;
			if ( $isActiveCache ) {
				$cacheFile = $tools['cacheFile'] ?? '';
				$expireAt  = $tools['expireAt'] ?? '';
				$time      = time();
				if ( !empty($expireAt) && $expireAt == 'never' ) {
					$expireAt = $time + 3600;
				} else {
					$expireAt = intval($expireAt) > 0 ? intval($expireAt) * 3600 : $time + 3600;
				}
				if ( !empty($cacheFile) ) {
					$cacheFile = $this->ensureCommandsCacheFile($cacheFile);
				}
				if ( file_exists($cacheFile) ) {
					$data     = json_decode(file_get_contents($cacheFile), true);
					$isExpire = isset($data['time']) && ($time - $data['time']) < $expireAt;
					if ( is_array($data) && $isExpire && isset($data['commands']) ) {
						return $data['commands'];
					} else {
						$commands = $this->listAllCommands();
						@file_put_contents($cacheFile, json_encode([
							'time'     => time(),
							'commands' => $commands
						]));

						return $commands;
					}
				}

			}
		}
		Helper::dd('else', $this->config);

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
		$found    = array_filter($commands, function ($cmd) use ($term) {
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

	public function _tools ($arg) : string {

		$arg     = trim($arg);
		$explode = explode(' ', $arg);
		$cmd     = $explode[0] ?? '';
		$arg     = $explode[1] ?? '';

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

		if ( $cmd === 'search' || $cmd === 'search-all' ) {
			$searchTerm = trim($arg);
			if ( empty($searchTerm) ) {
				return 'usage: app search <keyword>';
			}
			if ( $cmd === 'search' ) {
				return $this->searchInUsefulCommands($searchTerm);
			} else {
				return $this->searchAllCommands($searchTerm);
			}
		}

		return 'terminal.php: Not found commend';
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

}


/* Check if Request is Ajax */
if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' && isset($_POST['command']) ) {
	$command   = explode(' ', $_REQUEST['command'])[0];
	$arguments = array_slice(explode(' ', $_REQUEST['command']), 1);
	$path      = isset($_REQUEST['path']) ? $_REQUEST['path'] : '';
	$terminal  = new TerminalPHP($path, $config ?? []);

	if ( in_array($command, get_class_methods('CustomCommands')) ) {
		$result = CustomCommands::$command($arguments);
	} else {
		$command = $terminal->runCommand($_REQUEST['command']);
		$result  = $terminal->normalizeHtml($command);

	}
	$resp = json_encode([
		'result' => $result,
		'path'   => $terminal->pwd()
	]);

	exit($resp);
}

$terminal = new TerminalPHP();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Terminal.php</title>
    <link href="https://cdn.rawgit.com/rastikerdar/vazir-code-font/v1.1.2/dist/font-face.css" rel="stylesheet" type="text/css"/>
    <style>
        :root {
            --background-url: url('http://files.javadfathi.ir/terminal-background.jpeg');
            --font: 'Vazir Code', 'Vazir Code Hack';
            --font-size: 16px;
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

        terminal {
            display: block;
            width: 80vw;
            height: 80vh;
            position: relative;
            margin: 7rem auto;
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
            height: 92%;
            z-index: 1;
            overflow-x: hidden;
            overflow-y: auto;
            color: #ececec;
            font-size: var(--font-size);
        }

        terminal .content line {
            display: block;
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

<footer>Coded by <a href="https://github.com/smartwf">SmartWF</a></footer>

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
