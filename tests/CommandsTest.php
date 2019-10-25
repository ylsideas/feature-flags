<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Config;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class CommandsTest extends TestCase
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
    }

    /** @test */
    public function turnFeaturesOn()
    {
        Features::turnOff('my-feature');

        $this->assertFalse(Features::accessible('my-feature'));

        $this->artisan('feature:on', ['feature' => 'my-feature'])
            ->assertExitCode(0);

        $this->assertTrue(Features::accessible('my-feature'));
    }

    /** @test */
    public function turnFeaturesOff()
    {
        Features::turnOn('my-feature');

        $this->assertTrue(Features::accessible('my-feature'));

        $this->artisan('feature:off', ['feature' => 'my-feature'])
            ->assertExitCode(0);

        $this->assertFalse(Features::accessible('my-feature'));
    }

    /** @test */
    public function getFeatureState()
    {
        Features::turnOn('my-feature');

        $this->artisan('feature:state', ['feature' => 'my-feature'])
            ->expectsOutput('Feature `my-feature` is currently on')
            ->assertExitCode(0);

        Features::turnOff('my-feature');

        $this->artisan('feature:state', ['feature' => 'my-feature'])
            ->expectsOutput('Feature `my-feature` is currently off')
            ->assertExitCode(0);
    }
}
