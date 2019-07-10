<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
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

        Config::set('features.default', 'config');
    }

    /** @test */
    public function scheduleTasksWillSkipWhenFeatureIsOnAndSkippingWithFeature()
    {
        Features::turnOn('my-feature');

        /** @var Event $event */
        $event = (new Schedule())->command('list')
            ->sendOutputTo('')
            ->skipWithoutFeature('my-feature');

        $this->assertTrue($event->filtersPass($this->app));
    }

    /** @test */
    public function scheduleTasksWillSkipWhenFeatureIsOffAndSkippingWithFeature()
    {
        Features::turnOff('my-feature');

        /** @var Event $event */
        $event = (new Schedule())->command('list')
            ->sendOutputTo('')
            ->skipWithoutFeature('my-feature');

        $this->assertFalse($event->filtersPass($this->app));
    }

    /** @test */
    public function scheduleTasksWillSkipWhenFeatureIsOnAndSkippingWithoutFeature()
    {
        Features::turnOn('my-feature');

        /** @var Event $event */
        $event = (new Schedule())->command('list')
            ->sendOutputTo('')
            ->skipWithFeature('my-feature');

        $this->assertFalse($event->filtersPass($this->app));
    }

    /** @test */
    public function scheduleTasksWillSkipWhenFeatureIsOffAndSkippingWithoutFeature()
    {
        Features::turnOff('my-feature');

        /** @var Event $event */
        $event = (new Schedule())->command('list')
            ->sendOutputTo('')
            ->skipWithFeature('my-feature');

        $this->assertTrue($event->filtersPass($this->app));
    }
}
