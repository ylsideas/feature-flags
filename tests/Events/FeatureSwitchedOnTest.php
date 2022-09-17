<?php

namespace YlsIdeas\FeatureFlags\Tests\Events;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOn;

class FeatureSwitchedOnTest extends TestCase
{
    public function test_it_can_be_initialised(): void
    {
        $event = new FeatureSwitchedOn('my-feature', 'database');

        $this->assertInstanceOf(FeatureSwitchedOn::class, $event);
        $this->assertSame('my-feature', $event->feature);
    }
}
