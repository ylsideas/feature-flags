<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Contracts\Cache\Repository;
use Psr\SimpleCache\InvalidArgumentException;
use YlsIdeas\FeatureFlags\Contracts\Cacheable;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Support\GatewayCacheTest
 */
class GatewayCache
{
    protected ?int $ttl = null;

    public function __construct(protected Repository $repository, protected string $namespace, protected Cacheable $cacheable)
    {
    }

    public function hits(string $feature): bool
    {
        return $this->repository->has($this->generateKey($feature));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function result(string $feature): bool
    {
        return (bool) $this->repository->get($this->generateKey($feature));
    }

    public function store(string $feature, ?bool $result): void
    {
        $this->repository->put($this->generateKey($feature), $result, $this->ttl);
    }

    public function delete(string $feature): void
    {
        $this->repository->delete($this->generateKey($feature));
    }

    public function generateKey(string $feature): string
    {
        return $this->namespace . ':' . $this->cacheable->generateKey($feature);
    }

    public function configureTtl(?int $seconds): self
    {
        $this->ttl = $seconds;

        return $this;
    }
}
