<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase;
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

        View::addNamespace('testing', __DIR__.'/views');
    }

    /** @test */
    public function bladeDirectiveIncludesWhenFeatureIsOnAndExpectedOn()
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(true);

        $page = View::make('testing::features-on');

        $this->assertStringContainsString('feature is on', $page);
    }

    /** @test */
    public function bladeDirectiveExcludesWhenFeatureIsOffAndExpectedOn()
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(false);

        $page = View::make('testing::features-on');

        $this->assertStringNotContainsString('feature is on', $page);
    }

    /** @test */
    public function bladeDirectiveIncludesWhenFeatureIsOffAndExpectedOff()
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(false);

        $page = View::make('testing::features-off');

        $this->assertStringContainsString('feature is on', $page);
    }

    /** @test */
    public function bladeDirectiveExcludesWhenFeatureIsOnAndExpectedOff()
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(true);

        $page = View::make('testing::features-off');

        $this->assertStringNotContainsString('feature is on', $page);
    }
}
