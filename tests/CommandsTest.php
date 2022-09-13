<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Orchestra\Testbench\TestCase;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class CommandsTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FeatureFlagsServiceProvider::class,
        ];
    }

    public function test_turn_features_on(): void
    {
        Features::shouldReceive('turnOn')
            ->with('test', 'my-feature')
            ->once();

        $this->artisan('feature:on', ['gateway' => 'test', 'feature' => 'my-feature'])
            ->assertExitCode(0);
    }

    public function test_turn_features_off(): void
    {
        Features::shouldReceive('turnOff')
            ->with('test', 'my-feature')
            ->once();

        $this->artisan('feature:off', ['gateway' => 'test', 'feature' => 'my-feature'])
            ->assertExitCode(0);
    }
}
