<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Orchestra\Testbench\TestCase;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class SchedulingEventsMacroTest extends TestCase
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
    }

    public function test_schedule_tasks_will_skip_when_feature_is_on_and_skipping_with_feature(): void
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

    public function test_schedule_tasks_will_skip_when_feature_is_off_and_skipping_with_feature(): void
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

    public function test_schedule_tasks_will_skip_when_feature_is_on_and_skipping_without_feature(): void
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

    public function test_schedule_tasks_will_skip_when_feature_is_off_and_skipping_without_feature(): void
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
