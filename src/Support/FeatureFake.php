<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Traits\ForwardsCalls;
use PHPUnit\Framework\Assert;
use YlsIdeas\FeatureFlags\Contracts\Features;
use YlsIdeas\FeatureFlags\Events\FeatureAccessed;
use YlsIdeas\FeatureFlags\Events\FeatureAccessing;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Support\FeatureFakeTest
 */
class FeatureFake implements Features
{
    use ForwardsCalls;

    protected array $flagCounts = [];

    /**
     * @param array<string, bool|array> $featureFlags
     */
    public function __construct(protected Features $manager, protected array $featureFlags = [])
    {
    }

    public function accessible(string $feature): bool
    {
        Event::dispatch(new FeatureAccessing($feature));

        $featureValue = $this->featureFlags[$feature] ?? false;

        if (is_array($featureValue)) {
            $execution = $this->getCount($feature);

            // if the array has run out of values, then use the last position
            $featureValue = ($featureValue[$execution] ?? null) !== null ?
                $featureValue[$execution] :
                Arr::last($featureValue);
        }

        Arr::set($this->flagCounts, $feature, $this->getCount($feature) + 1);

        Event::dispatch(new FeatureAccessed($feature, $featureValue));

        return $featureValue;
    }

    public function assertAccessed(string $feature, ?int $count = null, string $message = '')
    {
        if ($count === null) {
            Assert::assertGreaterThan(0, $this->getCount($feature), $message);
        } else {
            Assert::assertSame($count, $this->getCount($feature), $message);
        }
    }

    public function assertNotAccessed(string $feature, string $message = '')
    {
        Assert::assertLessThan(1, $this->getCount($feature), $message);
    }

    public function assertAccessedCount(string $feature, int $count = 0, string $message = '')
    {
        $this->assertAccessed($feature, $count, $message);
    }

    public function __call(string $method, array $args)
    {
        return $this->forwardCallTo($this->manager, $method, $args);
    }

    protected function getCount(string $feature)
    {
        return Arr::get($this->flagCounts, $feature, 0);
    }
}
