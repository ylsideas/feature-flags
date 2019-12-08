<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class FeatureControllerTest extends TestCase
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
    public function publishesAllFeatures()
    {
        Features::routes();

        Features::turnOn('my-feature');
        Features::turnOff('my-second-feature');

        $this->getJson('/features')
            ->assertOk()
            ->assertExactJson([
                'features' => [
                    'my-feature' => true,
                    'my-second-feature' => false,
                ],
            ]);
    }
}
