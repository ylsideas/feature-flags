<?php

namespace YlsIdeas\FeatureFlags\Facades;

use Illuminate\Support\Facades\Facade;
use YlsIdeas\FeatureFlags\Manager;

/**
 * @see \YlsIdeas\FeatureFlags\Manager
 *
 * @method static array all()
 * @method static bool accessible(string $feature)
 * @method static turnOn(string $gateway, string $feature)
 * @method static turnOff(string $gateway, string $feature)
 * @method static bool usesValidations()
 * @method static bool usesScheduling()
 * @method static bool usesBlade()
 * @method static bool usesCommands()
 * @method static Manager noValidations()
 * @method static Manager noScheduling()
 * @method static Manager noBlade()
 * @method static Manager noCommands()
 */
class Features extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Manager::class;
    }
}
