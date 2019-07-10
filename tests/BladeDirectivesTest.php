<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class BladeDirectivesTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FeatureFlagsServiceProvider::class,
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        Config::set('features.default', 'config');

        View::addNamespace('testing', __DIR__.'/views');
    }

    /** @test */
    public function bladeDirectiveIncludesWhenFeatureIsOnAndExpectedOn()
    {
        Features::turnOn('my-feature');

        $page = View::make('testing::features-on');

        $this->assertStringContainsString('feature is on', $page);
    }

    /** @test */
    public function bladeDirectiveExcludesWhenFeatureIsOffAndExpectedOn()
    {
        Features::turnOff('my-feature');

        $page = View::make('testing::features-on');

        $this->assertStringNotContainsString('feature is on', $page);
    }

    /** @test */
    public function bladeDirectiveIncludesWhenFeatureIsOffAndExpectedOff()
    {
        Features::turnOff('my-feature');

        $page = View::make('testing::features-off');

        $this->assertStringContainsString('feature is on', $page);
    }

    /** @test */
    public function bladeDirectiveExcludesWhenFeatureIsOnAndExpectedOff()
    {
        Features::turnOn('my-feature');

        $page = View::make('testing::features-off');

        $this->assertStringNotContainsString('feature is on', $page);
    }
}
