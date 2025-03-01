<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Pipeline\Pipeline;
use Orchestra\Testbench\TestCase;
use RuntimeException;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;
use YlsIdeas\FeatureFlags\Manager;

class GatewayDriverTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FeatureFlagsServiceProvider::class,
        ];
    }

    public function test_throws_exception_creating_a_gateway_driver(): void
    {
        config()->set('features.pipeline', ['gate']);
        config()->set('features.gateways', [
            'gate' => [
                'driver' => 'gate',
            ],
        ]);

        $this->expectException(RuntimeException::class);

        $this->app->make(Manager::class)->pipeline();
    }

    public function test_create_a_gateway_driver(): void
    {
        config()->set('features.pipeline', ['gate']);
        config()->set('features.gateways', [
            'gate' => [
                'driver' => 'gate',
                'gate' => 'test',
            ],
        ]);

        $this->assertInstanceOf(Pipeline::class, $this->app->make(Manager::class)->pipeline());
    }
}
