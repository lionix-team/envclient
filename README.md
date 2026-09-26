# EnvClient for Laravel

[![Tests](https://github.com/lionix-team/envclient/actions/workflows/tests.yml/badge.svg)](https://github.com/lionix-team/envclient/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/lionix/envclient.svg)](https://packagist.org/packages/lionix/envclient)
[![License](https://img.shields.io/packagist/l/lionix/envclient.svg)](LICENSE.md)

Read, write and validate your `.env` file with artisan commands, Laravel validation rules and a fluent client.

- `php artisan env:set DB_CONNECTION mysql` — safely update a variable, validated against your rules
- `php artisan env:check` — validate the whole `.env` file (non-zero exit code on failure, great for CI/CD)
- `php artisan env:diff` / `env:sync` — keep `.env` in sync with `.env.example`
- `php artisan env:generate` — fill in the variables your deployment needs
- `EnvClient::useValidator(new DatabaseRules)->update([...])` — do the same from your code

## Requirements

| Package | PHP       | Laravel       |
| ------- | --------- | ------------- |
| 2.x     | 8.2 – 8.5 | 12.x, 13.x    |
| 1.x     | 7.2 – 8.x | 5.8 – 8.x     |

## Installation

```bash
composer require lionix/envclient
```

The service provider is auto-discovered. Publish the configuration with:

```bash
php artisan vendor:publish --tag=envclient
```

## Artisan commands

| Command                                         | Description                                                     |
| ----------------------------------------------- | --------------------------------------------------------------- |
| `env:get {key} [--reveal]`                      | Print the value of a variable (secrets are masked)              |
| `env:set {key} {value}`                         | Set a variable if it passes the configured validation rules     |
| `env:unset {keys*}`                             | Remove one or more variables                                    |
| `env:check`                                     | Validate all variables against the configured rules             |
| `env:empty`                                     | List the variables that have no value                           |
| `env:diff [--example=]`                         | Compare the file with `.env.example`                            |
| `env:sync [--example=] [--to-example]`          | Add the variables missing from `.env` (and optionally the example) |
| `env:generate [--force] [--dry-run] [--reveal]` | Fill in variables provided by the configured generators         |
| `env:restore [--force]`                         | Restore the file from its last backup                           |
| `make:envrule {name} [--force]`                 | Create a new validation rules class in `app/Env`                |
| `make:envgenerator {name} [--force]`            | Create a new generator class in `app/Env`                       |

All `env:*` commands except `env:restore` also accept [`--file`, `--encrypted`, `--key` and `--cipher`](#other-environment-files).
They return a non-zero exit code on failure, so they can be used in CI/CD pipelines.

## Basic usage

```bash
php artisan env:set APP_NAME "My Application"
php artisan env:get APP_NAME
php artisan env:unset OLD_FEATURE_FLAG LEGACY_API_URL
```

`env:set` replaces the variable if it exists or appends it to the end of the file. Values are quoted and
escaped when needed (spaces, `#`, quotes, backslashes), so they are always read back correctly.
After a change, `env()` returns the new value for the rest of the request or command. Configuration
values that were already resolved from the environment are not affected.

### Secrets

Values of variables matching the `hidden` patterns in `config/env.php` (by default `*PASSWORD*`, `*SECRET*`,
`*TOKEN*`, `*PRIVATE*`, `*_KEY` and `*_KEY_ID`) are printed as `********`. Pass `--reveal` to print them:

```bash
$ php artisan env:get DB_PASSWORD
********

$ php artisan env:get DB_PASSWORD --reveal
hunter2
```

## Keeping `.env` in sync with `.env.example`

After pulling changes that add new variables to `.env.example`:

```bash
$ php artisan env:diff
  WARN  Missing from the environment file:
  ⇂ NEW_FEATURE_FLAG

$ php artisan env:sync
  INFO  Added to the environment file:
  ⇂ NEW_FEATURE_FLAG
```

- `env:diff` lists the variables missing on either side and fails when `.env` misses any of them.
- `env:sync` copies the missing variables with the default values written in the example file.
  With `--to-example`, the variables missing from the example are added to it with empty values.
- Use `--example=path` to compare with another file.

## Validation

### Configuration

`config/env.php` lists the validator classes applied by `env:set`, `env:check` and `env:generate`:

```php
'rules' => [
    \App\Env\BaseEnvValidationRules::class,
],
```

Publishing the configuration also creates `app/Env/BaseEnvValidationRules.php`. Any
[Laravel validation rule](https://laravel.com/docs/validation#available-validation-rules) can be used:

```php
namespace App\Env;

use Lionix\EnvClient\Services\EnvValidator;

class BaseEnvValidationRules extends EnvValidator
{
    public function rules(): array
    {
        return [
            'APP_ENV' => ['required', 'in:local,staging,production'],
            'DB_PORT' => ['required_unless:DB_CONNECTION,sqlite', 'numeric'],
        ];
    }
}
```

```bash
$ php artisan env:set APP_ENV prod
   ERROR  The selected APP_ENV is invalid.

$ php artisan env:check
   ERROR  The selected APP_ENV is invalid.
```

`env:set` validates the new value together with the rest of the file, so rules can reference other
variables, but it only refuses the write when the variable being set is invalid.

### Ready-made rule sets

Add any of these to `rules` instead of writing the rules yourself:

| Class                                   | Checks                                                                                  |
| --------------------------------------- | --------------------------------------------------------------------------------------- |
| `Lionix\EnvClient\Rules\AppRules`       | `APP_NAME`, `APP_ENV`, a valid `APP_KEY`, `APP_DEBUG` (off in production), `APP_URL`    |
| `Lionix\EnvClient\Rules\DatabaseRules`  | `DB_CONNECTION` is a configured connection; host, port and database (unless SQLite)     |
| `Lionix\EnvClient\Rules\MailRules`      | `MAIL_MAILER` is a configured mailer; SMTP host and port; `MAIL_FROM_ADDRESS`           |
| `Lionix\EnvClient\Rules\QueueRules`     | `QUEUE_CONNECTION` and `CACHE_STORE` are configured; `SESSION_DRIVER` is supported      |
| `Lionix\EnvClient\Rules\RedisRules`     | `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PORT`, `REDIS_DB`                                  |
| `Lionix\EnvClient\Rules\AwsRules`       | AWS credentials and a well-formed `AWS_DEFAULT_REGION`                                  |

Connection, mailer and store names are checked against your own `config/*.php` files, so custom ones work.

```php
'rules' => [
    \Lionix\EnvClient\Rules\AppRules::class,
    \Lionix\EnvClient\Rules\DatabaseRules::class,
    \App\Env\BaseEnvValidationRules::class,
],
```

### Create more rule classes

```bash
php artisan make:envrule DatabaseEnvRules
php artisan make:envrule Services/MailEnvRules   # app/Env/Services/MailEnvRules.php
```

Then register them in `config/env.php`. Rule classes are resolved through the service container, so
constructor injection is supported. To customize the generated classes, publish the stubs with
`php artisan vendor:publish --tag=envclient-stubs`.

### Validate on boot

Set `validate_on_boot` (or `ENV_VALIDATE_ON_BOOT`) to validate the environment on every web request:

- `log` — log a warning with the validation errors.
- `exception` — stop the application with a `Lionix\EnvClient\Exceptions\InvalidEnvironmentException`.

Console commands are never affected, so you can still fix the file with `env:set`. Run `env:check` in
your deployment scripts to catch problems before a release goes live.

## Generating variables on deployment

Instead of asking someone to edit the `.env` file on every server, describe the variables your
application needs in generator classes and run `env:generate` as part of your deployment.

```bash
php artisan make:envgenerator DeploymentEnv
```

`app/Env/DeploymentEnv.php`

```php
namespace App\Env;

use Illuminate\Support\Str;
use Lionix\EnvClient\Services\EnvGenerator;

class DeploymentEnv extends EnvGenerator
{
    public function values(): array
    {
        return [
            'QUEUE_CONNECTION' => 'redis',
            'SESSION_DRIVER' => 'redis',
            'APP_KEY' => fn () => 'base64:'.base64_encode(random_bytes(32)),
            'WEBHOOK_SECRET' => fn () => Str::random(40),
        ];
    }
}
```

Register it in `config/env.php`:

```php
'generators' => [
    \App\Env\DeploymentEnv::class,
],
```

Then run it on deployment:

```bash
php artisan env:generate            # fill in missing or empty variables only
php artisan env:generate --dry-run  # show what would be written
php artisan env:generate --force    # overwrite existing values too
```

- By default only variables that are missing or empty are written, so values already configured on the
  server (in the `.env` file or the real environment) are never overwritten. Run it on every deployment.
- Closures are called only when their variable is actually written, so secrets are generated once.
  They are resolved through the service container, so they may type-hint dependencies.
- The values are validated against your `env.rules` classes first. If any of them is invalid, nothing is
  written and the command exits with a non-zero code.
- Generators are resolved through the service container and applied in order; later generators win.
- Secret values are masked in the output unless `--reveal` is passed.

## Other environment files

Every `env:*` command works on `.env` by default. Use `--file` for another file, relative to the
application's environment path or absolute:

```bash
php artisan env:set APP_URL https://staging.example.com --file=.env.staging
php artisan env:diff --file=.env.staging
```

### Encrypted files

Laravel's `env:encrypt` stores an encrypted copy of the file in `.env.encrypted` (or `.env.staging.encrypted`).
Pass `--encrypted` to read and update that copy directly, without decrypting it to disk next to your app:

```bash
php artisan env:get DB_PASSWORD --encrypted --key=base64:... --reveal
php artisan env:set DB_PASSWORD new-secret --file=.env.production --encrypted
```

The key defaults to the `LARAVEL_ENV_ENCRYPTION_KEY` variable, and `--cipher` defaults to `AES-256-CBC`,
the same defaults as `env:encrypt`. The contents are decrypted into a private temporary file that is
deleted as soon as the command finishes, and changes are encrypted back into the `.encrypted` file.

## Backups

Set `backup` to `true` in `config/env.php` to copy the file to `<file>.backup` before every change made
by this package, and restore it with:

```bash
php artisan env:restore            # asks for confirmation
php artisan env:restore --force
php artisan env:restore --file=.env.staging
php artisan env:restore --encrypted
```

Only the last version is kept. Laravel's default `.gitignore` already ignores `.env.backup`.

## Events

| Event                                                     | Dispatched when                                   | Properties                            |
| --------------------------------------------------------- | ------------------------------------------------- | ------------------------------------- |
| `Lionix\EnvClient\Events\EnvironmentFileUpdated`          | The file is changed by the package                | `path`, `set`, `forgotten`, `keys()`  |
| `Lionix\EnvClient\Events\EnvironmentVariablesGenerated`   | `env:generate` wrote variables                    | `path`, `keys`                        |

For example, to keep an audit log:

```php
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Lionix\EnvClient\Events\EnvironmentFileUpdated;

Event::listen(function (EnvironmentFileUpdated $event) {
    Log::info('Environment updated', ['file' => $event->path, 'keys' => $event->keys()]);
});
```

`set` contains the values as written to the file, so avoid logging it as-is.

## Using the client in code

Use the facade, or inject `Lionix\EnvClient\Interfaces\EnvClientInterface`:

```php
use Lionix\EnvClient\Facades\EnvClient;
use Lionix\EnvClient\Rules\DatabaseRules;

$client = EnvClient::useValidator(new DatabaseRules())
    ->update([
        'DB_HOST' => '127.0.0.1',
        'DB_DATABASE' => 'forge',
    ]);

if ($client->errors()->isNotEmpty()) {
    // $client->errors() is an Illuminate\Support\MessageBag
}

EnvClient::forget(['LEGACY_API_URL']);
```

> Every static facade call resolves a fresh client, so chain the calls that should share a validator
> and its errors (as above), or keep the returned instance.

### Client methods

| Method                                              | Description                                                                   |
| --------------------------------------------------- | ----------------------------------------------------------------------------- |
| `all(): array`                                      | All variables declared in the `.env` file with their values                   |
| `has(string $key): bool`                            | Whether the `.env` file declares the key                                      |
| `get(string $key): mixed`                           | Value of the variable (same casting as `env()`)                               |
| `set(array $values): static`                        | Queue values to be saved if they pass validation                              |
| `save(): static`                                    | Write the queued values to the `.env` file                                    |
| `update(array $values): static`                     | `set()` and `save()` in one step                                              |
| `forget(array $keys): static`                       | Remove variables from the `.env` file                                         |
| `validate(array $values): bool`                     | Validate values with the current validator                                    |
| `errors(): MessageBag`                              | All validation errors collected during the client lifetime                    |
| `useValidator(EnvValidatorInterface $v): static`    | Switch validator, keeping the errors collected so far                         |
| `useGetter(EnvGetterInterface $g): static`          | Replace the reader                                                            |
| `useSetter(EnvSetterInterface $s): static`          | Replace the writer                                                            |

Values passed to `set()` / `update()` may be strings, numbers, booleans (`true`/`false`), `null` or
`Stringable` objects. Invalid variable names and multi-line values throw an `InvalidArgumentException`.

`get()` and `all()` return the values from the runtime environment (like `env()`). When the configuration
is cached, or another file is targeted, they are read from the file itself.

### Building blocks

The client is composed of swappable services, bound in the container:

| Interface                                               | Default implementation                         |
| ------------------------------------------------------- | ---------------------------------------------- |
| `Lionix\EnvClient\Interfaces\EnvGetterInterface`        | `Lionix\EnvClient\Services\EnvGetter`          |
| `Lionix\EnvClient\Interfaces\EnvSetterInterface`        | `Lionix\EnvClient\Services\EnvSetter`          |
| `Lionix\EnvClient\Interfaces\EnvValidatorInterface`     | `Lionix\EnvClient\Services\EnvValidator`       |

Rebind any of them in your own service provider to change how the `.env` file is read, written or validated.
To support `forget()` and `env:unset`, a custom setter must implement `EnvForgetterInterface`.

## Upgrading from 1.x

1. Make sure your application runs on **PHP 8.2+** and **Laravel 12+**.
2. If you implemented the package interfaces yourself, update the signatures:
   - `EnvClientInterface` no longer declares a constructor, fluent methods return `static` and `get()` returns `mixed`.
   - `EnvGetterInterface::get()` returns `mixed`.
3. If you extended `EnvSetter`, `sanitize()` now accepts `mixed` and new `validateKey()` / `isQuoted()` helpers exist.
4. Scripts relying on `env:set`, `env:get` or `env:check` always exiting with `0` must now handle a non-zero exit code on failure.
5. Prefer the new `Lionix\EnvClient\Facades\EnvClient` facade and `Lionix\EnvClient\Services\*` classes; the
   `Lionix\EnvClient`, `Lionix\EnvGetter`, `Lionix\EnvSetter` and `Lionix\EnvValidator` aliases are kept for backwards compatibility.

See the [changelog](CHANGELOG.md) for the full list of changes.

## Contributing

```bash
composer test      # PHPUnit
composer analyse   # PHPStan (Larastan)
composer format    # Laravel Pint
```

## Credits

- [Stas Vartanyan](https://github.com/vaawebdev)
- [Lionix Team](https://github.com/lionix-team)

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
