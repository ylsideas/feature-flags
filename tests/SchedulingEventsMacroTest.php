<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Orchestra\Testbench\TestCase;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class SchedulingEventsMacroTest extends TestCase
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
    }

    /** @test */
    public function scheduleTasksWillSkipWhenFeatureIsOnAndSkippingWithFeature()
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(true);

        /** @var Event $event */
        $event = (new Schedule())->command('list')
            ->sendOutputTo('')
            ->skipWithoutFeature('my-feature');

        $this->assertTrue($event->filtersPass($this->app));
    }

    /** @test */
    public function scheduleTasksWillSkipWhenFeatureIsOffAndSkippingWithFeature()
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(false);

        /** @var Event $event */
        $event = (new Schedule())->command('list')
            ->sendOutputTo('')
            ->skipWithoutFeature('my-feature');

        $this->assertFalse($event->filtersPass($this->app));
    }

    /** @test */
    public function scheduleTasksWillSkipWhenFeatureIsOnAndSkippingWithoutFeature()
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(true);

        /** @var Event $event */
        $event = (new Schedule())->command('list')
            ->sendOutputTo('')
            ->skipWithFeature('my-feature');

        $this->assertFalse($event->filtersPass($this->app));
    }

    /** @test */
    public function scheduleTasksWillSkipWhenFeatureIsOffAndSkippingWithoutFeature()
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(false);

        /** @var Event $event */
        $event = (new Schedule())->command('list')
            ->sendOutputTo('')
            ->skipWithFeature('my-feature');

        $this->assertTrue($event->filtersPass($this->app));
    }
}
