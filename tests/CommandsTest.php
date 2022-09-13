<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Mockery\MockInterface;
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

    /** @test */
    public function turnFeaturesOn(): void
    {
        Features::shouldReceive('turnOn')
            ->with('test', 'my-feature')
            ->once();

        $this->artisan('feature:on', ['gateway' => 'test', 'feature' => 'my-feature'])
            ->assertExitCode(0);
    }

    /** @test */
    public function turnFeaturesOff(): void
    {
        Features::shouldReceive('turnOff')
            ->with('test', 'my-feature')
            ->once();

        $this->artisan('feature:off', ['gateway' => 'test', 'feature' => 'my-feature'])
            ->assertExitCode(0);
    }
}
