<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class BladeDirectivesTest extends TestCase
{
    protected function getPackageProviders($app): array
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

    public function test_blade_directive_includes_when_feature_is_on_and_expected_on(): void
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(true);

        $page = View::make('testing::features-on');

        $this->assertStringContainsString('feature is on', $page);
    }

    public function test_blade_directive_excludes_when_feature_is_off_and_expected_on(): void
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(false);

        $page = View::make('testing::features-on');

        $this->assertStringNotContainsString('feature is on', $page);
    }

    public function test_blade_directive_includes_when_feature_is_off_and_expected_off(): void
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(false);

        $page = View::make('testing::features-off');

        $this->assertStringContainsString('feature is on', $page);
    }

    public function test_blade_directive_excludes_when_feature_is_on_and_expected_off(): void
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(true);

        $page = View::make('testing::features-off');

        $this->assertStringNotContainsString('feature is on', $page);
    }
}
