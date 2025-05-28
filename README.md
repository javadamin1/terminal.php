# Terminal.php - Web-based Terminal Emulator for PHP

A lightweight and customizable PHP-based terminal emulator that lets you run shell commands from your browser.  
🛠 Forked and extended from [SmartWF's terminal.php](https://github.com/smartwf/terminal.php) with improvements in customization and security.

---

## 🚀 Features

- Execute server-side shell commands via web UI
- Define custom PHP-based commands
- Prevent execution of dangerous commands
- Ajax-based command execution for better UX
- Compatible with most shared hosts
- Clean HTML output formatting

---

## 🔐 Security Setup

To prevent unauthorized access, set a secure `KEY` inside `terminal.php`:

```php
const KEY = 'YourRandomSecureKey';
```

Access the terminal only via:

```
https://yourdomain.com/terminal.php?key=YourRandomSecureKey
```

---

## ⚙️ Hosting Tips

If you encounter the error:

```
fatal: $HOME not set
```

Set the environment variables at the top of the script:

```php
putenv("HOME=/tmp");
putenv("COMPOSER_HOME=/home/yourDomainName");
```

Replace `/home/yourDomainName` with the correct home directory for your hosting environment.

---

## 🧩 Custom Commands

You can define your own PHP-based commands inside the `CustomCommands` class:

```php
public static function hi($args) {
    return 'Hi ' . implode(' ', $args);
}
```

To call: `hi Javad`

---

## 🚫 Blocked Commands

For security, potentially dangerous commands (e.g. `rm`, `mv`, `wget`) are blocked by default.  
You can manage the list inside the `$blocked_commands` array.

---

## 📂 File Structure

- `terminal.php` - Main entry file for terminal interface
- `CustomCommands` - Define additional PHP commands
- `TerminalPHP` - Shell command runner and local command processor

---

## 📸 Screenshot

![Terminal UI Screenshot](assets/screenshot.png)
---

## 👨‍💻 Author

Maintained and extended by **[Javad Fathi](https://github.com/javadamin1)**  
Originally by [SmartWF](https://github.com/smartwf)

---

## 📝 License

MIT License
