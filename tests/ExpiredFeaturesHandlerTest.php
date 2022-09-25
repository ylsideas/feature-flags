<?php

namespace YlsIdeas\FeatureFlags\Tests;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Exceptions\FeatureExpired;
use YlsIdeas\FeatureFlags\ExpiredFeaturesHandler;

class ExpiredFeaturesHandlerTest extends TestCase
{
    public function test_it_can_be_initiated(): void
    {
        $handler = new ExpiredFeaturesHandler(['my-feature'], function (string $feature) {
            throw new FeatureExpired($feature);
        });

        $this->assertInstanceOf(
            \YlsIdeas\FeatureFlags\Contracts\ExpiredFeaturesHandler::class,
            $handler
        );
    }

    public function test_it_can_calls_a_handler_when_an_expired_feature_is_accessed(): void
    {
        $handler = new ExpiredFeaturesHandler(['my-feature'], $caller = new class () {
            public bool $called = false;

            public function __invoke(): void
            {
                $this->called = true;
            }
        });

        $handler->isExpired('my-feature');

        $this->assertTrue($caller->called);
    }

    public function test_it_does_not_can_call_a_handler_when_an_expired_feature_is_accessed(): void
    {
        $handler = new ExpiredFeaturesHandler(['my-feature'], $caller = new class () {
            public bool $called = false;

            public function __invoke(): void
            {
                $this->called = true;
            }
        });

        $handler->isExpired('my-other-feature');

        $this->assertFalse($caller->called);
    }
}
