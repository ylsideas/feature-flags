<?php

namespace YlsIdeas\FeatureFlags\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Testing\Fakes\Fake;
use YlsIdeas\FeatureFlags\Contracts\Features as FeaturesContract;
use YlsIdeas\FeatureFlags\Support\FeatureFake;

/**
 * @method static \Illuminate\Pipeline\Pipeline pipeline()
 * @method static bool accessible(string $feature)
 * @method static void turnOn(string $gateway, string $feature)
 * @method static void turnOff(string $gateway, string $feature)
 * @method static \static noBlade()
 * @method static \static noScheduling()
 * @method static \static noValidations()
 * @method static \static noCommands()
 * @method static \static noMiddlewares()
 * @method static \static noQueryBuilderMixin()
 * @method static \static configureDebugging(bool $value = true)
 * @method static bool usesBlade()
 * @method static bool usesValidations()
 * @method static bool usesScheduling()
 * @method static bool usesCommands()
 * @method static bool usesMiddlewares()
 * @method static bool usesDebugging()
 * @method static bool usesQueryBuilderMixin()
 * @method static \static callOnExpiredFeatures(array $expiredFeatures, callable|null $handler = null)
 * @method static \static applyOnExpiredHandler(\YlsIdeas\FeatureFlags\Contracts\ExpiredFeaturesHandler $handler)
 * @method static \static extend(string $driver, callable $builder)
 * @method static \YlsIdeas\FeatureFlags\Support\MaintenanceRepository maintenanceMode()
 * @method static void assertAccessed(string $feature, int|null $count = null, string $message = '')
 * @method static void assertNotAccessed(string $feature, string $message = '')
 * @method static void assertAccessedCount(string $feature, int $count = 0, string $message = '')
 *
 * @see \YlsIdeas\FeatureFlags\Manager
 * @see \YlsIdeas\FeatureFlags\Support\FeatureFake
 */
class Features extends Facade
{
    /**
     * Replace the bound instance with a fake.
     * @param array<string, bool|array> $flagsToFake
     */
    public static function fake(array $flagsToFake = []): FeatureFake
    {
        $manager = static::isFake()
            ? static::getFacadeRoot()->manager()
            : static::getFacadeRoot();

        static::swap($fake = new FeatureFake($manager, $flagsToFake));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return FeaturesContract::class;
    }
}
