<?php

namespace YlsIdeas\FeatureFlags\Facades;

use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Facade;
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
 * @method static \self callOnExpiredFeatures(array $expiredFeatures, callable|null $handler = null)
 * @method static \self applyOnExpiredHandler(\YlsIdeas\FeatureFlags\Contracts\ExpiredFeaturesHandler $handler)
 * @method \self extend(string $driver, callable $builder)
 *
 * @see \YlsIdeas\FeatureFlags\Manager
 */
class Features extends Facade
{
    /**
     * Replace the bound instance with a fake.
     * @param array<string, bool|array> $flagsToFake
     */
    public static function fake(array $flagsToFake = []): FeatureFake
    {
        static::swap($fake = new FeatureFake(static::getFacadeRoot(), $flagsToFake));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return FeaturesContract::class;
    }
}
