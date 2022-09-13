<?php

namespace YlsIdeas\FeatureFlags\Tests\Events;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOff;

class FeatureSwitchedOffTest extends TestCase
{
    public function test_it_can_be_initialised(): void
    {
        $event = new FeatureSwitchedOff('my-feature', 'database');

        $this->assertInstanceOf(FeatureSwitchedOff::class, $event);
        $this->assertSame('my-feature', $event->feature);
        $this->assertSame('database', $event->gateway);
    }
}
