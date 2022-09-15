<?php

namespace YlsIdeas\FeatureFlags\Tests\Events;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOn;

class FeatureSwitchedOnTest extends TestCase
{
    public function testItCanBeInitialised()
    {
        $event = new FeatureSwitchedOn('my-feature', 'database');

        $this->assertInstanceOf(FeatureSwitchedOn::class, $event);
        $this->assertSame('my-feature', $event->feature);
    }
}
