
# EnvClient for Laravel 6+
Manage and validate environmental variables with artisan console commands and facades


## Installation

```
composer require lionix/envclient
```


## Artisan commands

| Signature | Description |
|--|--|
| `env:get {key}` | Prints .env variable value |
| `env:set {key} {value}` | Sets .env variable if validation rules are passed |
| `env:check` | Check all env variables for validness |
| `env:empty` | Print empty .env variables |
| `make:envrule {name}` | Create a new .env validation rules |


## Basic usage

Set an environment variable using `env:set` artisan command. 

```
php artisan env:set EXAMPLE_ENV_VARIABLE "example value"
```

The command will modify your environment file by replacing or adding the given key to it.

### Validate environment variables

If you want to apply validation rules to environmental variables before `env:set` command will modify the file you will have to publish command package configuration files.


```
php artisan vendor:publish --provider="Lionix\EnvClient\Providers\EnvClientServiceProvider" --tag="config"
```

The command will create `config/env.php`
```php
<?php

return [

    /**
     * Validation classes which contain environment rules
     * applied by env artisan commands.
     * 
     * Add your validation classes created by 
     * `php artisan make:envrule` command to apply their rules
     * 
     * @var array
     */
    "rules" => [
        \App\Env\BaseEnvValidationRules::class
    ]
];
```

and `app/Env/BaseEnvValidationRules.php`

```php
<?php

namespace App\Env;

use Lionix\EnvValidator;

class BaseEnvValidationRules extends EnvValidator
{
    /**
     * Validation rules that apply to the .env variables.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            //
        ];
    }
}
```
By adding validation rules into `rules` method return value you will apply them to `env:set` command. 

```php
...
public function rules() : array
{
    return [
        "DB_CONNECTION" => "required|in:mysql,sqlite"
    ];
}
...
```

This way if you try to set an invalid value to the `DB_CONNECTION` variable with `env:set` command, the console will print out an error 

```
$ php artisan env:set DB_CONNECTION SomeInvalidValue
The selected DB_CONNECTION is invalid.
```

If your environment file was modified you can run `env:check` command which will check all variables for validness and print out the results.

```
$ php artisan env:check
The selected DB_CONNECTION is invalid.
```


## Create a new environmental validation rules

### Run the `make:envrule` command

By default, the script will generate a class in `App/Env` namespace.


#### Example:
```
php artisan make:envrule DatabaseEnvRules
```

`app/Env/DatabaseEnvRules.php`

```php
<?php

namespace App\Env;

use Lionix\EnvValidator;

class DatabaseEnvRules extends EnvValidator
{
    /**
     * Validation rules that apply to the .env variables.
     *
     * @return array
     */
    public function rules() : array
    {
        return [
            //
        ];
    }
}
```

### Specify validation rules:

```php
...
public function rules() : array
{
    return [
        "DB_CONNECTION" => "requried|in:mysql,sqlite,pgsql,sqlsrv"
        "DB_HOST" => "requried",
        "DB_PORT" => "requried|numeric",
        "DB_DATABASE" => "requried",
        "DB_USERNAME" => "requried",
        "DB_PASSWORD" => "requried"
    ];
}
...
```

### Apply the rules:

You can add the `DatabaseEnvRules` class to `env.php` configuration file at the `rules` key. That way all the rules specified in the class will affect package artisan commands.

`config/env.php`  

```php

<?php

return [

    /**
     * Validation classes which contain environment rules
     * applied by env artisan commands.
     * 
     * Add your validation classes created by 
     * `php artisan make:envrule` command to apply their rules
     * 
     * @var array
     */
    "rules" => [
        \App\Env\BaseEnvValidationRules::class
        \App\Env\DatabaseEnvRules::class // <- our database rules
    ]
];
```

Or you can use `Lionix\EnvClient` Facade to validate the input with given validation rules:

```php
...
$client = new \Lionix\Envclient();
$client
    ->useValidator(new \App\Env\DatabaseEnvRules())
    ->update($databaseCredintnails);

if ($client->errors()->isNotEmpty()) {
    // handle errors
} else {
    // success, the variables are updated
}
...
```


## Facades

### Lionix\EnvClient

#### Properties:

- protected **$getter** : *Lionix\EnvClient\Interfaces\EnvGetterInterface*

- protected  **$setter** : *Lionix\EnvClient\Interfaces\EnvSetterInterface*

- protected **$validator** : *Lionix\EnvClient\Interfaces\EnvValidatorInterface*

#### Methods:

- `void` : **__construct()**  
  Create a new instance of EnvClient using default dependencies

- `self` : **useGetter(_Lionix\EnvClient\Interfaces\EnvGetterInterface_ $getter)**  
  Set client getter dependency

- `self` : **useSetter(_Lionix\EnvClient\Interfaces\EnvSetterInterface_ $setter)**  
  Set setter dependency

- `self` : **useValidator(_Lionix\EnvClient\Interfaces\EnvValidatorInterface_ $validator)**  
  Set validator dependency merging current errors with the validator errors

- `array` : **all()**  
  Get all env variables from the environmental file

- `bool` : **has(_string $key_)**  
  Check if the environmental file contains the key

- `mixed` : **get(_string $key_)**  
  Get the env variable using the key (returns the output of `Illuminate\Support\Env` get method)

- `self` : **set(_array $values_)**  
  Set the environmental variables at runtime if validation rules passed

- `self` : **save()**  
  Save previously set variables to the environmental file

- `self` : **update()**  
  If validation rules passed then set and save variables to the environmental file 

- `bool` : **validate(_array_ $values)**  
  Check values validness and retrieve passed status

- `Illuminate\Support\MessageBag` : **errors()**  
  Get all validation errors occurred during the class lifetime

### Lionix\EnvGetter

#### Methods:

- `void` : **__construct()**  
  Create a new instance of EnvGetter
  
- `mixed` : **get(_string_ $key)**  
  Get the env variable using the key (returns the output of `Illuminate\Support\Env` get method)
  
- `array` : **all()**  
  Get all env variables from the environmental file

- `bool` : **has(_string_ $key)**  
  Check if the environmental file contains the key

### Lionix\EnvSetter

#### Properties:

- protected **$variablesToSet** : *array*

#### Methods:

- `void` : **__construct()**  
  Create a new instance of EnvSetter

- `void` : **set(_array_ $values)**  
  Merge given values with variablesToSet property
  
- `void` : **save()**  
  Save all variables previously set by the set method to the environmental file
  
- protected `string` : **sanitize(_string_ $value)**  
  Sanitize input values

### Lionix\EnvValidator

#### Properties:

- protected **$errors** : *Illuminate\Support\MessageBag*

#### Methods:

- `void` : **__construct()**  
  Create a new instance of EnvValidator

- `array` : **rules()**  
  Returns class validation rules
  
- `bool` : **validate(_array_ $values)**  
  Validate given values
  
- `Illuminate\Support\MessageBag` : **errors()**  
  Get validator errors
  
- `void` : **mergeErrors(_Illuminate\Support\MessageBag_ $errors)**  
  Merge given MessageBag with current errors