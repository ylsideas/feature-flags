<?php

namespace YlsIdeas\FeatureFlags\Facades;

use Illuminate\Support\Facades\Facade;
use YlsIdeas\FeatureFlags\Contracts\ExpiredFeaturesHandler;
use YlsIdeas\FeatureFlags\Contracts\Features as FeaturesContract;
use YlsIdeas\FeatureFlags\Manager;
use YlsIdeas\FeatureFlags\Support\FeatureFake;

/**
 * @see \YlsIdeas\FeatureFlags\Manager
 *
 * @method static bool accessible(string $feature)
 * @method static void turnOn(string $gateway, string $feature)
 * @method static void turnOff(string $gateway, string $feature)
 * @method static bool usesValidations()
 * @method static bool usesScheduling()
 * @method static bool usesBlade()
 * @method static bool usesCommands()
 * @method static bool usesMiddlewares()
 * @method static bool usesQueryBuilderMixin()
 * @method static Manager noValidations()
 * @method static Manager noScheduling()
 * @method static Manager noBlade()
 * @method static Manager noCommands()
 * @method static Manager noMiddleware()
 * @method static Manager noQueryBuilderMixin()
 * @method static Manager callOnExpiredFeatures(array $features, callable $handler)
 * @method static Manager applyOnExpiredHandler(ExpiredFeaturesHandler $handler)
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
