<?php

namespace YlsIdeas\FeatureFlags\Tests\Events;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOn;

class FeatureSwitchedOnTest extends TestCase
{
    /** @test */
    public function itCanBeInitialised()
    {
        $event = new FeatureSwitchedOn('my-feature', 'database');

        $this->assertInstanceOf(FeatureSwitchedOn::class, $event);
        $this->assertSame('my-feature', $event->feature);
    }
}
