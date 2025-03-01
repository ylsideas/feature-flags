<?php

namespace YlsIdeas\FeatureFlags\Tests\Support;

use Illuminate\Contracts\Cache\Repository;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Contracts\Cacheable;
use YlsIdeas\FeatureFlags\Support\GatewayCache;

class GatewayCacheTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_hits_the_cache_correct(): void
    {
        $repository = Mockery::mock(Repository::class);
        $cachable = Mockery::mock(Cacheable::class);

        $cachable->shouldReceive('generateKey')
            ->with('my-feature')
            ->once()
            ->andReturn('key');

        $repository->shouldReceive('has')
            ->with('test:key')
            ->once()
            ->andReturn(true);

        $cache = new GatewayCache($repository, 'test', $cachable);

        $this->assertTrue($cache->hits('my-feature'));
    }

    public function test_it_stores_with_a_ttl_correctly(): void
    {
        $repository = Mockery::mock(Repository::class);
        $cachable = Mockery::mock(Cacheable::class);

        $cachable->shouldReceive('generateKey')
            ->with('my-feature')
            ->once()
            ->andReturn('key');

        $repository->shouldReceive('put')
            ->with('test:key', true, 1000)
            ->once()
            ->andReturn(true);

        $cache = (new GatewayCache($repository, 'test', $cachable))
            ->configureTtl(1000);

        $cache->store('my-feature', true);
    }
}
