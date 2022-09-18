# Feature Flags for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ylsideas/feature-flags.svg?style=flat-square)](https://packagist.org/packages/ylsideas/feature-flags)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/ylsideas/feature-flags/run-tests?label=tests)](https://github.com/ylsideas/feature-flags/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/ylsideas/feature-flags/Check%20&%20fix%20styling?label=code%20style)](https://github.com/ylsideas/feature-flags/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ylsideas/feature-flags.svg?style=flat-square)](https://packagist.org/packages/ylsideas/feature-flags)

A Feature flag is at times referred to as a feature toggle or feature switch. Ultimately it's a coding strategy 
to be used along with source control to make it easier to continuously integrate and deploy. The idea of 
the flags works by essentially safe guarding sections of code from executing if a feature flag isn't in a switched 
on state.

This package aims to make implementing such flags across your application a great deal easier by providing solutions
that work with not only your code but your routes, blade files, task scheduling and validations.

## Upgrading

This project is currently at version 2 and is somewhat different to version 1. If you are using Laravel 9 and PHP8
you should aim to use version 2. Version 1 is no longer supported. There is an [upgrade guide for moving
from version 1 to version 2](UPGRADE.md).

## Installation

You can install the package via composer:

```bash
composer require ylsideas/feature-flags:^2.0
```

Once installed you should publish the config with the following command.

```bash
php artisan vendor:publish --provider="YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider" --tag=config
```

You can customise the `features.php` config in a number of ways.

### Configuration

Feature Flags is incredibly extensible. Including the ability to set which sources to test for if a feature is enabled
this is done via a pipeline. These pipes are referred to as Gateways. Each Gateway is visited until a Boolean result
is returned.

You may configure as many Gateways as you want to be a part of your pipeline and also use as many of the same driver as
you like. The build in drivers are `database`, `in_memory`, `redis` and `gate`.

#### Database Driver
To use the Database driver you will need to add the migration. You can do this by
using the publish command.

```bash
php artisan vendor:publish --tag=features-migration
```

This driver will use the nominated Database connection & table for your gateway.

```php
'gateways' => [
    'database' => [
        'driver' => 'database',
        'cache' => [
            'ttl' => 600,
        ],
        'connection' => env('FEATURE_FLAG_DATABASE_CONNECTION'),
        'table' => env('FEATURE_FLAG_DATABASE_TABLE', 'features'),
    ],
],
```

You may also use the on/off commands to affect the state of the features with this driver.

#### Redis Driver

This driver will use the nominated Redis connection for your gateway. You may add a prefix as well
making it easier to delete the keys stored by the gateway.

```php
'gateways' => [
    'redis' => [
        'driver' => 'redis',
        'prefix' => env('FEATURE_FLAG_REDIS_PREFIX', 'features'),
        'connection' => env('FEATURE_FLAG_REDIS_CONNECTION', 'default'),
    ],
],
```

You may also use the on/off commands to affect the state of the features with this driver.

#### InMemory Driver

This driver will use a `.features.php` file in the base of the project to configure the in memory features.
You may wish to also create a `.features.php.dist` file. This file will be used when a `.features.php` does not
exist.

```php
'gateways' => [
    'in_memory' => [
        'file' => env('FEATURE_FLAG_IN_MEMORY_FILE', '.features.php'),
        'driver' => 'in_memory',
        'caching' => [
            'ttl' => 300,
        ],
    ],
],
```

You can create the `.features.php` file using the following command:

```bash
php artisan vendor:publish --provider="YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider" --tag=inmemory-config
```

You can then use the returning function to provide an array which will be used by the InMemory gateway.

```php
<?php

use Illuminate\Contracts\Foundation\Application;

/**
 * @returns array<string, bool>
 */
return static function (Application $app): array {
    return [
        'my.feature.flag' => true,
    ];
};
```

You can not use the on/off commands to affect the state of the features with this driver.

#### Gate Driver

The gate driver will allow you to use a [gate defined in Laravel](https://laravel.com/docs/9.x/authorization#gates). 
This gate will then receive the feature being accessed, you may apply logic based on a user or guest accessing the site through
the gate chosen.

```php
'gateways' => [
    'gate' => [
        'driver' => 'gate',
        'gate' => env('FEATURE_FLAG_GATE_GATE', 'feature'),
        'guard' => env('FEATURE_FLAG_GATE_GUARD'),
        'cache' => [
            'ttl' => 600,
        ],
    ],
],
```

```php
Gate::define('feature', function (?User $user, $feature) {
    return true;
});
```

The gate behaviour is different to other gateways in that it will always provide a true or false result. If you put this
gateway before any others their will always be a result meaning gateways after the gate will not be executed.

You can not use the on/off commands to affect the state of the features with this driver.

### Caching with Gateways

You may implement a cache per gateway. This is done by providing a cache key.

```php
'gateways' => [
    'database' => [
        'driver' => 'database',
        'cache' => [
            'store' => 'file' //Optional. Tells which cache store to use, will otherwise use the default cache.
            'ttl' => 300, // Optional. Tells how long to cache for, defaults to 5 minutes.
        ],
        'connection' => env('FEATURE_FLAG_DATABASE_CONNECTION'),
        'table' => env('FEATURE_FLAG_DATABASE_TABLE', 'features'),
    ],
],
```

The default is for all items to be cached for 5 minutes. You can set the value to `null` if you wish
to not have the cache items expire. This is not recommended.

### Filtering features by Gateway

You may also tell some gateways to not response to certain feature prefixes. For example
if you have a feature called `system.my-feature` you can apply a filter of `system.*`, this
will mean only features with the prefix of `system.` will interact with that gateway.
You may also flip that logic with `!system.*` meaning that any feature that does start with
that prefix will not interact with the gateway and move onto the next.

```php
'gateways' => [
    'mysql' => [
        'driver' => 'database',
        'connection' => 'mysql',
        'filter' => 'system.*',
    ],
    'sqlite' => [
        'driver' => 'database',
        'connection' => 'sqlite',
        'filter' => '!system.*',
    ],
],
```

You may apply multiple filters in the configuration of a gateway using an `array`.

```php
'gateways' => [
    'mysql' => [
        'driver' => 'database',
        'filter' => ['system.*', 'user.*'],
    ],
]
```

### Turning off functionality

Everything is enabled by default but if you want to turn off several features add the following method calls 
to the boot method of `app/Providers/AppServiceProvider.php` in your project.

```php
Features::noBlade();
Features::noScheduling();
Features::noValidations();
Features::noCommands();
Features::noMiddlewares();
```

## Usage

### Checking feature accessibility

You can use the accessible method to check if a feature is on or off.

```php
Features::accessible('my-feature') // returns true or false
```

### Blade Views

the `@feature` blade directive is a simple `@if` shortcut to hide or display certain parts of the view
depending on the state of the feature. A second argument flips the state e.g. it will display the contents
of the if statement, if the feature is off.

```php
@feature('my-feature')
    <p>Your feature flag is turned on.</p>
@endfeature

@feature('my-feature', false)
    <p>Your feature flag is turned off.</p>
@endfeature
```

### Routing Middleware

The middleware will cause routes to be blocked if the specified feature does not have the correct state.

```php
Route::get('/', 'SomeController@get')->middleware('feature:my-feature')
Route::get('/', 'SomeController@get')->middleware('feature:my-feature,on')
Route::get('/', 'SomeController@get')->middleware('feature:my-feature,off,404')
```

### Validation Rules

Fields can be marked as required depending on if the feature is in a particular state.

```php
Validator::make([
    'name' => 'Peter',
    'place' => 'England',
    'email' => 'peter.fox@ylsideas.co',
], [
    'name' => 'requiredWithFeature:my-feature', // required
    'place' => 'requiredWithFeature:my-feature,on', // required
    'email' => 'requiredWithFeature:my-feature,off', // not required
]);
```

### Task Scheduling

Using the following will determine if a task will run on schedule depending on the state of the
feature.

```php
$schedule->command('emails:send Peter --force')
    ->skipWithFeature('my-feature')
    
$schedule->command('emails:send Peter --force')
    ->skipWithoutFeature('my-other-feature')    
```

### Artisan Commands

You may run the following commands to toggle the on or off state of the feature.

```bash
php artisan feature:on <gateway> <feature>

php artisan feature:off <gateway> <feature>
```

### Implementing your Own Gateway Drivers

You can create your own gateway drivers. To do so you will need to make your own class
which implements that `YlsIdeas\FeatureFlags\Contracts\Gateway` interface.

```php
public class CustomGateway implements \YlsIdeas\FeatureFlags\Contracts\Gateway
{
    public function __construct(protected $option)
    {
    }

    public function accessible(string $feature): ?bool
    {
        return true; // Decision logic should occur here
    }

}
```

You will then need to register this with the Features manager. This can be done
using the following code placed inside a register method of a Service Provider.

```php
public function register()
{
    $this->app->extend(\YlsIdeas\FeatureFlags\Manager::class, function (\YlsIdeas\FeatureFlags\Manager $manager) {
        return $manager->extend('custom', function (array $config) {
            return new CustomGateway($config['option']);
        });
    })
}
```

Then you only need use it in your `features.php` config.

```php
'gateways' => [
    'custom' => [
        'driver' => 'custom',
        'option' => true,
        'cache' => [
            'ttl' => 600,
        ],
    ],
],
```

You may also make your driver be `Toggleable` and `Cacheable`

## Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email peter.fox@ylsideas.co instead of using the issue tracker.

## Credits

- [Peter Fox](https://github.com/ylsideas)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
