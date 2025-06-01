# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
