# Changelog

All notable changes to `lionix/envclient` are documented in this file.

## [2.0.0] - 2026-09-26

A modernization release targeting current PHP and Laravel versions. See the
[upgrade guide](README.md#upgrading-from-1x) for migration steps.

### Requirements

- PHP **8.2 – 8.5** (was `^7.2.5|^8.0`).
- Laravel **12.x and 13.x** (was `>=6.20.12`).
- The package now depends on the individual `illuminate/*` components instead of `laravel/framework`.

### Added

- `Lionix\EnvClient\Facades\EnvClient` facade. Each static call resolves a fresh client, so chain calls that should share validators and errors.
- `export KEY=value` lines are recognised when reading and preserved when writing.
- The `.env` file is created if it does not exist when saving.
- Values are quoted and escaped so they are always parsed back correctly by `vlucas/phpdotenv` (spaces, `#`, quotes and backslashes). Already quoted values are kept as-is.
- `bool`, `null`, numeric and `Stringable` values are accepted by the setter.
- Invalid variable names and multi-line values throw an `InvalidArgumentException` instead of corrupting the file.
- `make:envrule` supports nested names (`Database/MysqlRules`), the `--force` option and a publishable stub (`vendor:publish --tag=envclient-stubs`).
- `envclient` / `envclient-config` publish tags (the old `config` tag still works).
- Classes listed in `env.rules` are verified to implement `EnvValidatorInterface`.
- GitHub Actions test matrix for PHP 8.2–8.5 and Laravel 12–13.

### Changed

- All commands return proper exit codes: `env:check` and `env:set` return a non-zero code on validation failure, and `env:get` does so for unknown variables — handy in CI/CD pipelines.
- `env:set` validates the new value together with the rest of the file, so rules for other variables (e.g. `required`) no longer silently block the write, and rules such as `required_if` can reference other variables.
- `env:set` and `env:check` resolve validators through the service container, so validators may use constructor injection.
- `env:check` prints a warning and succeeds when no rules are configured (previously an error).
- Console output uses Laravel's console components.
- Container bindings are registered in `register()` instead of `boot()`.
- The whole codebase uses `declare(strict_types=1)`, typed properties, constructor property promotion and native return types.
- `EnvClientInterface` fluent methods now return `static`; `get()` returns `mixed`.

### Fixed

- New variables were appended as the literal text `$key=$value` instead of the actual key and value.
- Values containing `$1`, `\1` and similar sequences were mangled by regex back-references.
- Keys that only partially matched (e.g. `MY_APP_NAME`, or a commented `# APP_NAME=`) could prevent the real key from being written.
- `env:get` reported `false`, `0` and empty values as "not found".
- `env:empty` reported `false` and `null` values as empty.
- `env:set` reported success even when the value was not saved.
- Values that were already quoted were double-quoted.
- `EnvValidator` ran the validation twice per call.

### Removed

- Support for PHP < 8.2 and Laravel < 12.
- The constructor declaration from `EnvClientInterface`.
- `composer.lock` is no longer committed.

## [1.1.3] and earlier

- PHP 8 support, Laravel 5.8+ support and the initial release.

[2.0.0]: https://github.com/lionix-team/envclient/compare/1.1.3...v2.0.0
[1.1.3]: https://github.com/lionix-team/envclient/releases/tag/1.1.3
