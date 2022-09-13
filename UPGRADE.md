# Upgrading

## Version 1 to Version 2

There's been a number of changes with the Feature Flag package. Some of them may cause difficulties for those using
the version 1 to be able to migrate to version to as these will be breaking changes.

The most important thing to state is the Facade itself and the API to check if a feature is accessible has not been 
changed. All
the points where you have put flags within your app should not be affected in this upgrade. 

The database schema for the database driver has not been changed either.

### Philosophy of Version 2

The original version 1 package was made in 2019. While it's been fairly popular, it never quite lived up to what I
wanted it to be. There's also been a few support issues where people have requested features which I felt the previous
version didn't work well with.

Version 2 ditches repositories of features for gateways instead.

A gateway is a single stop of many along the journey to assessing a feature flag. Each gateway has a driver behind it allowing for
a gateway to use the same technologies but with a different configuration. For example, you can now implement multiple
database gateways that look at different tables or even on different connections. Want to access a local SQLite DB
and then your MySQL database if you get nothing from the SQLite connection? Sure, why not.

There's also no longer a chain repository, because the whole package works like a chain. You now have a `pipeline`
option in the which specifies the order of the gateways.

Previously in version one, to implement caching you had to use the Chain repository, or implement your own system.
Now you can just specify your own per gateway and how to store it.

### Updating your config

You are likely best off removing your original `features.php` config and performing a vendor publish of the new config.
From there you can set the `pipeline`. If you were previously only using the database, redis or config drivers then
you just need to set the `pipleine` to be `['database']` etc.

If you were using the chain repository the default configuration for it as a pipeline would look like `['in_memory',
'redis', 'database']`.

You'll notice there is now an option for a cache per gateway, this allows for you to customise how results from one
gateway are cached and for how long, the cache keys will be namespaced by the name of the gateway even if you use
the same store, there will not be any conflicts.

Another feature is if you only want some gates to handle features with a particular prefix, you may provide a pattern
in the gateway configuration or a set of patterns that will be used to block the gateway from being inspected and
then continue onto the next pipe.

### The difference between the Config Repository and the InMemory Gateway

The database and redis drivers have no real difference. The Config Repository now gets its values from a different
source. There is no `feature` key in the `features.php` config. Instead, you should use the vendor publish command to
create a `.features.php` file at the base of your project with which you can store the values. It's simple enough to
copy and paste your original values. You can have the advantage that you can inject any services from the container
into your `.features.php` config whereas before you were limited to just .ENV values.

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

### Package Options & Middleware

The package options to turn off functionality that is typically on by default can be changed by setting.

### Implementing your own Gateways vs. Repositories

The biggest change will be to those who have implemented their own `YlsIdeas\FeatureFlags\Contracts\Repository` 
in version 1. Originally you only
needed to implement one interface. You now have multiple interfaces although the only interface you need is 
`YlsIdeas\FeatureFlags\Contracts\Gateway`. Once this is implemented you may also implement a 
`YlsIdeas\FeatureFlags\Contracts\Toggleable` and/or `YlsIdeas\FeatureFlags\Contracts\Cacheable` interface. The 
first allows for the Gateway to be used be the console commands for turning on and off features. The second is for
allowing Gateways to be cached.

### Removal of the Features Controller/Route

In version 1 a route and controller existed that could allow for HTTP clients (like from a frontend) to fetch all the 
features and all the
states they were in. This has now been removed as the Gate driver does not behave in this manner because the results
are contextual to the feature you are using. A feature like this may return in future versions but for now if you use
this you will need to either not upgrade or implement your own controller.

# Issues with Upgrading

If you feel there is a mistake in these docs on upgrading or something that needs clarifying
please open an Issue.


