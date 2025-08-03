# Terminal.php - Web-based Terminal Emulator for PHP

A lightweight and customizable PHP-based terminal emulator that lets you run shell commands from your browser.  
🛠 Forked and extended from [SmartWF's terminal.php](https://github.com/smartwf/terminal.php) with improvements in customization and security.

---

## 📸 Screenshot

![Terminal UI Screenshot](assets/screenshot.png)

---

## 🚀 Features

- Execute server-side shell commands via web UI
- Define custom PHP-based commands
- Prevent execution of dangerous commands
- Ajax-based command execution for better UX
- Compatible with Laravel (CSRF/auth supported)
- Clean HTML output formatting
- Tools discovery & caching (with filtering/search)
- 🔧 Install developer tools like `composer`, `node` via `tools install`

---

## 📦 Tools Installer

> ⚙️ You can also upgrade the terminal or installed tools using the `tools upgrade` command. Type `tools help` for more info.


### ▶️ Example Usage

```bash
tools install composer
```

This will:

1. Download the tool from the official source or from your own **online JSON-based package repository** (coming soon)
2. Make it executable
3. Move it to a proper binary directory (e.g., `tools/bin`)
4. Create a symlink for easy execution (`composer` will be available globally within the terminal)

> ℹ️ Tools are defined inside a remote or local JSON-based repository, and you can easily extend it.

### 🛠 Sample tool definition

Here’s a sample `composer` install script as used internally:

```json
{
  "composer": {
    "version": "2.7.0",
    "install": [
      {
        "action": "download",
        "url": "https://getcomposer.org/download/latest-stable/composer.phar",
        "key": "downloaded_file"
      },
      {
        "action": "shell",
        "cmd": "chmod +x {downloaded_file}"
      },
      {
        "action": "mv",
        "target": "{downloaded_file}",
        "path": "composer/{downloaded_file}",
        "key": "move_file"
      },
      {
        "action": "symlink",
        "target": "{move_file}",
        "link": "composer"
      }
    ]
  }
}
```

---

You can also create your own custom repository and add tools like `nvm`, `php-cs-fixer`, `wp-cli`, etc.

```bash
tools install nvm
tools install wp-cli
```

The terminal will parse the JSON, execute installation steps safely, and make tools globally usable inside your web terminal.

### 🌐 Future Support: Remote Tool Repository

In future releases, tools will be fetched dynamically from an online repository like:


---

## 🔐 Security Setup

To **prevent unauthorized access**, you **must set a secure `KEY`** inside `terminal.php`.  
The terminal will **not work with the default key**.

```php
const KEY = 'YourRandomSecureKey';
```

Access the terminal only via:

```
https://yourdomain.com/terminal.php?key=YourRandomSecureKey
# For Laravel integration:
https://yourdomain.com/terminal?key=YourRandomSecureKey
```

> ⚠️ **Important:**  
> The terminal will be **disabled** if the default key is not changed.  
> Make sure to use a long, random, and secret key to prevent unauthorized access.

---

## ⚙️ Hosting Tips

If you encounter the error:

```
fatal: $HOME not set
```

You don't need to modify the script directly.

Instead, create a file named `.terminal_env` in your project root (or home directory):

<pre><code class="language-bash">
# ~/.terminal_env
HOME=tools/home
COMPOSER_HOME=tools/home/.composer
</code></pre>

This file allows you to define environment variables like `HOME`, `COMPOSER_HOME`, etc.

These values will be automatically loaded and injected into the terminal environment using `putenv()`.

> ℹ️ **Default location:**  
> `terminal.php` will look for `.terminal_env` next to the script itself.

---

## ⚙️ Configuration

All config options are inside `terminal.php`:

```php
$config = [
    'laravelMode'     => false, // Set true if using with Laravel
    'cacheFile'       => __DIR__ . '/cache/cache.json', // change to your cache path
    'temporaryCache'  => 'cookie', // Options: none, cookie, session
    'tools'           => [
        'cache'  => 'month', // Options: forever, day, week, month
        'useful' => [ // Tools to auto-detect
            'git', 'composer', 'php', 'npm', 'node', 'yarn',
            'curl', 'wget', 'htop', 'top', 'ping', 'vim', 'nano',
            'ssh', 'scp', 'zip', 'unzip', 'tar', 'make', 'gcc',
            'git-lfs', 'python3', 'pip3', 'telnet', 'gzip', 'g++'
        ]
    ],
    'blockedCommands' => [
        // Add risky commands you want to block
        //'rm', 'mv', 'chmod', 'wget', 'curl', 'cp'
    ],
    'checkUpdate'     => 'day', // Options: none, day, week, month
    'debugMode'       => false // set to true if you want debug and test
];
```

---

## 🧩 Custom Commands

You can define your own PHP-based commands inside the `CustomCommands` class:

```php
public static function hi($args) {
    return 'Hi ' . implode(' ', $args);
}
```

To call in terminal: `hi Javad`

---

## 🚫 Block Dangerous Commands

Protect your server by blocking commands like:

```php
$config = [
    ...
    'blockedCommands' => ['rm', 'mv', 'chmod', 'wget', 'curl', 'cp'],
];
```

Blocked commands return a warning and are not executed.

---

## 📂 File Structure

- `terminal.php` - Main web terminal entry file
- `cache/cache.json` - Stores detected tools and update check data

---

## ⚙️ Laravel Integration

Want to integrate the terminal into your Laravel app and keep it secure?

### ✅ Setup

1. **Enable Laravel mode**

In `terminal.php`:

```php
'laravelMode' => true,
```

2. **Move UI to a Blade template**

Create: `resources/views/terminal.blade.php`  
Copy Content from `terminal.php` into this Blade file.

3. **Secure the route**

In `routes/web.php`:

```php
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/terminal', function () {
    return view('terminal'); // or use a custom view path
})->middleware('auth');
```

✅ This ensures CSRF protection and allows only logged-in users to access the terminal.

---

## 👨‍💻 Author

Maintained and extended by **[Javad Fathi](https://github.com/javadamin1)**  
Originally by [SmartWF](https://github.com/smartwf)

---

## 📝 License

MIT License
## 🔗 Links

- GitHub: [github.com/javadamin1/terminal.php](https://github.com/javadamin1/terminal.php)
- Issues: [Report here](https://github.com/javadamin1/terminal.php/issues)

