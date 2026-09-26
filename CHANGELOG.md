# Changelog

All notable changes to `lionix/envclient` are documented in this file.

## [2.2.0] - 2026-09-26

### Added

- `env:diff` and `env:sync` commands to compare and synchronise the environment file with `.env.example`.
- `env:unset` command and `EnvClient::forget()` to remove variables (`EnvForgetterInterface`, implemented by `EnvSetter`).
- `env:restore` command and the `backup` option, which copies the file to `<file>.backup` before every change.
- `--file` option on all `env:*` commands to work on another environment file.
- `--encrypted`, `--key` and `--cipher` options to read and update files encrypted with Laravel's `env:encrypt`
  without writing the decrypted contents next to the application.
- Secret values (`hidden` patterns in `config/env.php`) are masked in `env:get` and `env:generate` output; `--reveal` prints them.
- `validate_on_boot` option (`log` or `exception`) to validate the environment on every web request, with the new
  `InvalidEnvironmentException`.
- Ready-made rule sets: `AppRules`, `DatabaseRules`, `MailRules`, `QueueRules`, `RedisRules` and `AwsRules`.
- `EnvironmentFileUpdated` and `EnvironmentVariablesGenerated` events.
- `env:generate` lists the values it writes (masked) next to each variable.
- PHPStan (Larastan, level 8) and Laravel Pint checks in CI, Dependabot for GitHub Actions and `.gitattributes`
  to keep tests out of distributed archives.

### Changed

- **`env:get` masks secret values by default.** Scripts reading secrets with `env:get` must pass `--reveal`.
- Saving the file updates the runtime environment, so `env()` returns the new value for the rest of the request
  or command.
- When the configuration is cached (and `.env` is therefore not loaded) or another file is targeted, values are
  read from the file itself instead of the runtime environment.
- The file is not rewritten when a save would not change it.
- `illuminate/encryption` is now a direct dependency.

## [2.1.0] - 2026-09-26

### Added

- `env:generate` command that fills in environment variables provided by generator classes, e.g. on deployment ([#5](https://github.com/lionix-team/envclient/issues/5)).
  - Writes only missing or empty variables by default; `--force` overwrites existing values and `--dry-run` shows what would change.
  - Closure values are resolved lazily through the container, so secrets are generated only when needed.
  - Generated values are validated against `env.rules`; nothing is written if any of them is invalid.
- `make:envgenerator` command, `Lionix\EnvClient\Services\EnvGenerator` base class and `EnvGeneratorInterface`.
- `generators` key in `config/env.php`.

### Changed

- `vlucas/phpdotenv` is now a direct dependency (it was only pulled in through `laravel/framework`).

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

[2.2.0]: https://github.com/lionix-team/envclient/compare/2.1.0...2.2.0
[2.1.0]: https://github.com/lionix-team/envclient/compare/2.0.0...2.1.0
[2.0.0]: https://github.com/lionix-team/envclient/compare/1.1.3...2.0.0
[1.1.3]: https://github.com/lionix-team/envclient/releases/tag/1.1.3
