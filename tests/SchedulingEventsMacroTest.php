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

    public function testScheduleTasksWillSkipWhenFeatureIsOnAndSkippingWithFeature()
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

    public function testScheduleTasksWillSkipWhenFeatureIsOffAndSkippingWithFeature()
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

    public function testScheduleTasksWillSkipWhenFeatureIsOnAndSkippingWithoutFeature()
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

    public function testScheduleTasksWillSkipWhenFeatureIsOffAndSkippingWithoutFeature()
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
