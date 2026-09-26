# EnvClient for Laravel

[![Tests](https://github.com/lionix-team/envclient/actions/workflows/tests.yml/badge.svg)](https://github.com/lionix-team/envclient/actions/workflows/tests.yml)
[![Latest Version](https://img.shields.io/packagist/v/lionix/envclient.svg)](https://packagist.org/packages/lionix/envclient)
[![License](https://img.shields.io/packagist/l/lionix/envclient.svg)](LICENSE.md)

Read, write and validate your `.env` file with artisan commands, Laravel validation rules and a fluent client.

- `php artisan env:set DB_CONNECTION mysql` — safely update a variable, validated against your rules
- `php artisan env:check` — validate the whole `.env` file (non-zero exit code on failure, great for CI/CD)
- `EnvClient::useValidator(new DatabaseEnvRules)->update([...])` — do the same from your code

## Requirements

| Package | PHP       | Laravel       |
| ------- | --------- | ------------- |
| 2.x     | 8.2 – 8.5 | 12.x, 13.x    |
| 1.x     | 7.2 – 8.x | 5.8 – 8.x     |

## Installation

```bash
composer require lionix/envclient
```

The service provider is auto-discovered.

## Artisan commands

| Command                          | Description                                                        |
| -------------------------------- | ------------------------------------------------------------------ |
| `env:get {key}`                  | Print the value of a variable                                      |
| `env:set {key} {value}`          | Set a variable if it passes the configured validation rules        |
| `env:check`                      | Validate all variables against the configured rules                |
| `env:empty`                      | List the variables that have no value                              |
| `make:envrule {name} [--force]`  | Create a new validation rules class in `app/Env`                   |

`env:get`, `env:set` and `env:check` return a non-zero exit code on failure.

## Basic usage

```bash
php artisan env:set APP_NAME "My Application"
```

The command replaces the variable if it exists or appends it to the end of the file. Values are
quoted and escaped when needed (spaces, `#`, quotes, backslashes), so they are always read back correctly.

```bash
php artisan env:get APP_NAME
My Application
```

## Validation

### Publish the configuration

```bash
php artisan vendor:publish --tag=envclient
```

This creates two files:

`config/env.php`

```php
return [
    'rules' => [
        \App\Env\BaseEnvValidationRules::class,
    ],
];
```

`app/Env/BaseEnvValidationRules.php`

```php
namespace App\Env;

use Lionix\EnvClient\Services\EnvValidator;

class BaseEnvValidationRules extends EnvValidator
{
    public function rules(): array
    {
        return [
            //
        ];
    }
}
```

### Add rules

Any [Laravel validation rule](https://laravel.com/docs/validation#available-validation-rules) can be used:

```php
public function rules(): array
{
    return [
        'APP_ENV' => ['required', 'in:local,staging,production'],
        'APP_DEBUG' => ['required', 'boolean'],
        'DB_CONNECTION' => ['required', 'in:mysql,pgsql,sqlite,sqlsrv'],
        'DB_PORT' => ['required_unless:DB_CONNECTION,sqlite', 'numeric'],
    ];
}
```

Every class listed in `config/env.php` is applied by `env:set` and `env:check`:

```bash
$ php artisan env:set DB_CONNECTION oracle
   ERROR  The selected DB_CONNECTION is invalid.

$ php artisan env:check
   ERROR  The selected DB_CONNECTION is invalid.
```

`env:set` validates the new value together with the rest of the file, so rules can reference other
variables, but it only refuses the write when the variable being set is invalid.

### Create more rule classes

```bash
php artisan make:envrule DatabaseEnvRules
php artisan make:envrule Services/MailEnvRules   # app/Env/Services/MailEnvRules.php
```

Then register them in `config/env.php`:

```php
'rules' => [
    \App\Env\BaseEnvValidationRules::class,
    \App\Env\DatabaseEnvRules::class,
],
```

Rule classes are resolved through the service container, so constructor injection is supported.
To customize the generated class, publish the stub with `php artisan vendor:publish --tag=envclient-stubs`.

## Using the client in code

Use the facade, or inject `Lionix\EnvClient\Interfaces\EnvClientInterface`:

```php
use App\Env\DatabaseEnvRules;
use Lionix\EnvClient\Facades\EnvClient;

$client = EnvClient::useValidator(new DatabaseEnvRules())
    ->update([
        'DB_HOST' => '127.0.0.1',
        'DB_DATABASE' => 'forge',
    ]);

if ($client->errors()->isNotEmpty()) {
    // $client->errors() is an Illuminate\Support\MessageBag
}
```

> Every static facade call resolves a fresh client, so chain the calls that should share a validator
> and its errors (as above), or keep the returned instance.

### Client methods

| Method                                              | Description                                                                   |
| --------------------------------------------------- | ----------------------------------------------------------------------------- |
| `all(): array`                                      | All variables declared in the `.env` file with their runtime values           |
| `has(string $key): bool`                            | Whether the `.env` file declares the key                                      |
| `get(string $key): mixed`                           | Runtime value of the variable (same casting as `env()`)                       |
| `set(array $values): static`                        | Queue values to be saved if they pass validation                              |
| `save(): static`                                    | Write the queued values to the `.env` file                                    |
| `update(array $values): static`                     | `set()` and `save()` in one step                                              |
| `validate(array $values): bool`                     | Validate values with the current validator                                    |
| `errors(): MessageBag`                              | All validation errors collected during the client lifetime                    |
| `useValidator(EnvValidatorInterface $v): static`    | Switch validator, keeping the errors collected so far                         |
| `useGetter(EnvGetterInterface $g): static`          | Replace the reader                                                            |
| `useSetter(EnvSetterInterface $s): static`          | Replace the writer                                                            |

Values passed to `set()` / `update()` may be strings, numbers, booleans (`true`/`false`), `null` or
`Stringable` objects. Invalid variable names and multi-line values throw an `InvalidArgumentException`.

> `get()` and `all()` return the values loaded when the application booted. Changes written to the file
> are picked up on the next request or command.

### Building blocks

The client is composed of three swappable services, bound in the container:

| Interface                                               | Default implementation                         |
| ------------------------------------------------------- | ---------------------------------------------- |
| `Lionix\EnvClient\Interfaces\EnvGetterInterface`        | `Lionix\EnvClient\Services\EnvGetter`          |
| `Lionix\EnvClient\Interfaces\EnvSetterInterface`        | `Lionix\EnvClient\Services\EnvSetter`          |
| `Lionix\EnvClient\Interfaces\EnvValidatorInterface`     | `Lionix\EnvClient\Services\EnvValidator`       |

Rebind any of them in your own service provider to change how the `.env` file is read, written or validated.

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

## Testing

```bash
composer test
```

## Credits

- [Stas Vartanyan](https://github.com/vaawebdev)
- [Lionix Team](https://github.com/lionix-team)

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
