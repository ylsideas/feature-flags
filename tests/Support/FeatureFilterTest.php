<?php

namespace YlsIdeas\FeatureFlags\Tests\Support;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Support\FeatureFilter;

class FeatureFilterTest extends TestCase
{
    public function test_it_can_be_initialised(): void
    {
        $component = new FeatureFilter([]);

        $this->assertInstanceOf(FeatureFilter::class, $component);
    }

    /**
     * @dataProvider successfulPatterns
     */
    public function test_it_fails_filters_correctly(string $feature, array $filters): void
    {
        $component = new FeatureFilter($filters);

        $this->assertTrue($component->fails($feature));
    }

    /**
     * @dataProvider failurePatterns
     */
    public function test_it_does_not_fail_filters_correctly(string $feature, array $filters): void
    {
        $component = new FeatureFilter($filters);

        $this->assertFalse($component->fails($feature));
    }

    public function successfulPatterns(): \Generator
    {
        yield 'simple' => ['my-feature', ['system.*']];
        yield 'advanced' => ['my-feature', ['system.*', 'my-feature-1.*']];
        yield 'more advanced' => ['my-feature', ['system.*', '!my-feature']];
    }

    public function failurePatterns(): \Generator
    {
        yield 'simple' => ['my-feature.1', ['my-feature.*']];
    }
}
