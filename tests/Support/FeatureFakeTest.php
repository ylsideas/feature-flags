<?php

namespace YlsIdeas\FeatureFlags\Tests\Support;

use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\AssertionFailedError;
use YlsIdeas\FeatureFlags\Contracts\Features as FeaturesContract;
use YlsIdeas\FeatureFlags\Events\FeatureAccessed;
use YlsIdeas\FeatureFlags\Events\FeatureAccessing;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;
use YlsIdeas\FeatureFlags\Manager;
use YlsIdeas\FeatureFlags\Support\FeatureFake;

class FeatureFakeTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FeatureFlagsServiceProvider::class,
        ];
    }

    public function test_it_can_be_initialised(): void
    {
        $manager = \Mockery::mock(Manager::class);
        $fake = $this->getFake($manager, ['my-feature' => true]);

        $this->assertInstanceOf(FeatureFake::class, $fake);
    }

    public function test_it_can_be_swapped_via_the_facade(): void
    {
        $fake = Features::fake(['my-feature' => true]);

        $this->assertInstanceOf(FeatureFake::class, $fake);
        $this->assertTrue(Features::accessible('my-feature'));
    }

    public function test_it_can_be_fake_accessibility_results(): void
    {
        Event::fake();
        $manager = \Mockery::mock(Manager::class);
        $fake = $this->getFake($manager,  ['my-feature' => true]);

        $this->assertTrue($fake->accessible('my-feature'));
    }

    public function test_it_can_be_fake_accessibility_results_from_the_container(): void
    {
        Event::fake();
        Features::fake(['my-feature' => true]);

        $this->assertTrue(app()->call(fn (FeaturesContract $accessible): bool => $accessible->accessible('my-feature')));
    }

    public function test_it_can_be_fake_accessibility_results_if_no_value_is_provided(): void
    {
        Event::fake();
        $manager = \Mockery::mock(Manager::class);
        $fake = $this->getFake($manager,  []);

        $this->assertFalse($fake->accessible('my-feature'));
    }

    public function test_it_can_be_fake_accessibility_results_with_an_array(): void
    {
        Event::fake();
        $manager = \Mockery::mock(Manager::class);
        $fake = $this->getFake($manager,  ['my-feature' => [false, true]]);

        $this->assertFalse($fake->accessible('my-feature'));
        $this->assertTrue($fake->accessible('my-feature'));
        $this->assertSame(2, $fake->getCount('my-feature'));
    }

    public function test_it_can_be_fake_accessibility_results_with_an_array_using_the_last_position(): void
    {
        Event::fake();
        $manager = \Mockery::mock(Manager::class);
        $fake = $this->getFake($manager,  ['my-feature' => [false, true]]);

        $this->assertFalse($fake->accessible('my-feature'));
        $this->assertTrue($fake->accessible('my-feature'));
        $this->assertTrue($fake->accessible('my-feature'));
        $this->assertSame(3, $fake->getCount('my-feature'));
    }

    public function test_it_counts_how_often_a_feature_is_accessed(): void
    {
        Event::fake();
        $manager = \Mockery::mock(Manager::class);
        $fake = $this->getFake($manager,  ['my-feature' => true]);

        $this->assertSame(0, $fake->getCount('my-feature'));
        $fake->accessible('my-feature');
        $this->assertSame(1, $fake->getCount('my-feature'));
    }

    public function test_it_allows_for_asserting_if_a_feature_has_been_accessed(): void
    {
        $this->expectException(AssertionFailedError::class);
        Event::fake();
        $manager = \Mockery::mock(Manager::class);
        $fake = $this->getFake($manager,  ['my-feature' => true]);

        $this->assertSame(0, $fake->getCount('my-feature'));
        $fake->assertAccessed('my-feature');
    }

    public function test_it_allows_for_asserting_if_a_feature_has_not_been_accessed(): void
    {
        $this->expectException(AssertionFailedError::class);
        Event::fake();
        $manager = \Mockery::mock(Manager::class);
        $fake = $this->getFake($manager,  ['my-feature' => true]);

        $fake->accessible('my-feature');
        $this->assertSame(1, $fake->getCount('my-feature'));
        $fake->assertNotAccessed('my-feature');
    }

    public function test_it_allows_for_asserting_if_a_feature_has_used_multiple_times(): void
    {
        $this->expectException(AssertionFailedError::class);
        Event::fake();
        $manager = \Mockery::mock(Manager::class);
        $fake = $this->getFake($manager,  ['my-feature' => true]);

        $fake->accessible('my-feature');
        $fake->accessible('my-feature');
        $fake->accessible('my-feature');
        $this->assertSame(3, $fake->getCount('my-feature'));
        $fake->assertAccessedCount('my-feature', 2);
    }

    public function test_it_fires_events_still(): void
    {
        Event::fake();

        $manager = \Mockery::mock(Manager::class);
        $fake = $this->getFake($manager,  ['my-feature' => true]);

        $fake->accessible('my-feature');

        Event::assertDispatched(FeatureAccessing::class);
        Event::assertDispatched(FeatureAccessed::class);
    }

    public function test_it_can_switch_a_feature_multiple_times(): void
    {
        Features::fake(['my-feature' => true]);
        Features::fake(['my-feature' => false]);

        $this->assertFalse(Features::accessible('my-feature'));
    }

    protected function getFake($manager, $features): \YlsIdeas\FeatureFlags\Support\FeatureFake
    {
        return new class ($manager, $features) extends FeatureFake {
            public function getCount(string $feature)
            {
                return parent::getCount($feature);
            }
        };
    }
}
