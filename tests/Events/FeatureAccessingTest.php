<?php

namespace YlsIdeas\FeatureFlags\Tests\Events;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Events\FeatureAccessing;

class FeatureAccessingTest extends TestCase
{
    public function test_it_can_be_initialised(): void
    {
        $event = new FeatureAccessing('my-feature');

        $this->assertInstanceOf(FeatureAccessing::class, $event);
        $this->assertSame('my-feature', $event->feature);
    }
}
