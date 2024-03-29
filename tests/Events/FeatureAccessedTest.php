<?php

namespace YlsIdeas\FeatureFlags\Tests\Events;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Events\FeatureAccessed;

class FeatureAccessedTest extends TestCase
{
    public function test_it_can_be_initialised(): void
    {
        $event = new FeatureAccessed('my-feature', true);

        $this->assertInstanceOf(FeatureAccessed::class, $event);
        $this->assertSame('my-feature', $event->feature);
        $this->assertTrue($event->result);
    }
}
