# Feature Flags for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ylsideas/feature-flags.svg?style=flat-square)](https://packagist.org/packages/ylsideas/feature-flags)
[![Build Status](https://img.shields.io/travis/ylsideas/feature-flags/master.svg?style=flat-square)](https://travis-ci.org/ylsideas/feature-flags)
[![Quality Score](https://img.shields.io/scrutinizer/g/ylsideas/feature-flags.svg?style=flat-square)](https://scrutinizer-ci.com/g/ylsideas/feature-flags)
[![Code Coverage](https://scrutinizer-ci.com/g/ylsideas/feature-flags/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ylsideas/feature-flags/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/ylsideas/feature-flags.svg?style=flat-square)](https://packagist.org/packages/ylsideas/feature-flags)
[![StyleCI](https://github.styleci.io/repos/196839364/shield?branch=master)](https://github.styleci.io/repos/196839364)

A Feature flag is sometimes also referred to as a feature toggle or feature switch. Ultimately it's a coding strategy 
to be used along with source control to make it easier to continuous integrate and continuous deployment. The idea of 
the flags works by essentially safe guarding sections of code from executing if a feature flag isn't in a switched 
on state.

This package aims to make implementing such flags across your application a great deal easier by providing solutions
that work with not only your code but your routes, blade files, task scheduling and validations.

## Installation

You can install the package via composer:

```bash
composer require ylsideas/feature-flags
```

Once installed you should publish the config with the following command.

```bash
php artisan vendor:publish --provider=YlsIdeas\\FeatureFlags\\FeatureFlagsServiceProvider --tag=config
```

You can customise the `features.php` config in a number of ways. By default four storage drivers
for the feature flags are provided, config, database, redis and chain. the first three are pretty straight forward
but the chain is essentially a composite that allows you to store across all three. For example you might want
to query a feature that's hardcoded in the config. If it does not exist it will then go on to check redis.
If it's not stored there, then it'll check the database. Afterwards it can update the other sources to improve
flag checking times.

To use the Database driver you will need to add the migration. You can do this by
using the publish command.

```bash
php artisan vendor:publish --tag=features-migration
```

Everything is enabled by default but if you want to turn off several features add the following method calls 
to the boot method of `app/Providers/AppServiceProvider.php` in your project.

```php
Features::noBlade();
Features::noScheduling();
Features::noValidations();
Features::noCommands();
```

To install the middleware you'll have to add it to your `$routeMiddleware` inside `app/Http/Kernel.php` file.

```php
protected $routeMiddleware = [
    'feature' => \YlsIdeas\FeatureFlags\Middleware\FeatureFlagState::class,
];
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
of the if statement if the feature is off.

```php
@feature('my-feature')
    <p>Your feature flag is turned on.
@endfeature

@feature('my-feature', false)
    <p>Your feature flag is turned off.
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
    'name' => 'Peter'
    'place' => 'England',
    'email' => 'peter.fox@ylsideas.co'
], [
    'name' => 'requiredWithFeature:my-feature' // required
    'place' => 'requiredWithFeature:my-feature,on' // required
    'email => 'requiredWithFeature:my-feature,off' // not required
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
php artisan feature:on my-feature

php artisan feature:off my-feature
```

To find out the current state of the feature within the context of a
console command, run the following:

```bash
php artisan feature:state my-feature
```

### Testing

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

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
