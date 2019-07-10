<?php

namespace YlsIdeas\FeatureFlags\Tests\Events;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Events\FeatureSwitchedOff;

class FeatureSwitchedOffTest extends TestCase
{
    /** @test */
    public function itCanBeInitialised()
    {
        $event = new FeatureSwitchedOff('my-feature');

        $this->assertInstanceOf(FeatureSwitchedOff::class, $event);
        $this->assertSame('my-feature', $event->feature);
    }
}
