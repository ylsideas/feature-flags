<?php

namespace YlsIdeas\FeatureFlags\Tests\Events;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Events\FeatureAccessed;

class FeatureAccessedTest extends TestCase
{
    /** @test */
    public function itCanBeInitialised()
    {
        $event = new FeatureAccessed('my-feature');

        $this->assertInstanceOf(FeatureAccessed::class, $event);
        $this->assertSame('my-feature', $event->feature);
    }
}
