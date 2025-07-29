# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).


## [1.3.0] - 2025-07-29

### Added
- 🧩 Support for installing version-specific tools via `tools install <tool>@<version>`
- 📦 Extended JSON structure to support multiple versions per tool (e.g. `node`, `composer`)
- 🔍 Introduced `tools search -r <term>` to search in remote list of installable tools with version info
- 🔎 Added `tools search -a <term>` to search all available system commands in PATH
- 🧠 Context placeholders like `{version}` now supported across all install steps

### Changed
- 🆙 Updated `tools help` output to reflect new commands and clarify usage of `-r` and `-a` flags
- 🔄 Replaced `tools search-all` with `tools search -a` for consistency and simplicity
- 🧱 Refactored internal config resolution to cleanly handle default and specific versions

### Fixed
- ❌ Improved error handling when install target directory doesn’t exist or isn't writable
- 📛 Fixed duplicate version listing when multiple steps share same tool version
- 📁 Ensured proper cleanup of downloaded archives (e.g. `.tar.gz`) after successful install

## [1.2.0] - 2025-07-28

### Added
- 🔧 Introduced `tools install <tool>` command to install tools like `composer`, `nvm`, etc.
- 🧰 Installer supports JSON-based tool definitions with multiple actions: `download`, `shell`, `mv`, `symlink`
- 🔄 Placeholder context system (`{downloaded_file}`, `{move_file}`) for chaining install steps
- 📁 Configurable install paths: `tools/bin`, `tools/download` via `.env` file or config
- 🌐 `.terminal_env` support added to define environment variables like `HOME`, `COMPOSER_HOME`, `PATH`
- 📦 Prepared for future support of online JSON repositories for installable tools

### Changed
- Improved `README.md` with `📦 Tools Installer` usage section
- Refactored `executeInstallStep()` logic to support dynamic installation pipelines
- Enhanced `tools` command to recognize new subcommand `install`

### Fixed
- `$PATH` and `$HOME` environment visibility issue in non-interactive shells
- Proper symlink handling with fallback and cleanup

---
## [1.1.0] - 2025-06-12

🎉 **First stable release with important improvements**

### 🔍 New Features
- ✅ Added update checker (configurable via `'checkUpdate'` in config)
- 📦 Smart and manageable caching system (supports `'cookie'`, `'session'`)

### ⚠️ Changes
- ❌ Removed wildcard command execution due to conflicts
- 🔒 Improved security defaults and directory structure

### 📚 Also includes
- Clean and structured `README.md` with clearer instructions
---
## [1.0.1] - 2025-05-29

### Security
- Enforced secure `KEY` validation to restrict unauthorized access.
- Added brute-force protection via session-based attempt limit.

## [Unreleased]
- Add Windows compatibility support (planned)
- Implement Windows environment checks and tests
---
## [1.0.0] - 2025-05-28

### Added
- Laravel integration: blade rendering, CSRF protection, and auth middleware
- `laravelMode` config key to enable/disable Laravel-specific behavior
- Terminal tool discovery system with cache and useful-tool filtering
- `tools` command with `list`, `la`, and `search` subcommands
- Support for defining PHP-based custom commands via `CustomCommands` class
- AJAX-based command execution for smoother user experience
- Security mechanism to force KEY replacement before use

### Changed
- Improved README documentation with Laravel setup guide
- Restructured config array for better readability
- Moved static assets to cleaner paths

### Security
- Terminal access now completely blocked if default KEY is not changed
- Dangerous shell commands like `rm`, `mv`, `wget`, etc., are blocked by default

### Fixed
- `$HOME` unset issue on some shared hosting environments
- Minor CSS and JS bugs in terminal UI

---

## [Unreleased]
- Add Windows compatibility support (planned)
- Implement Windows environment checks and tests

