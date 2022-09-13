<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Contracts\Cache\Repository;
use YlsIdeas\FeatureFlags\Contracts\Cacheable;

class GatewayCache
{
    protected ?int $ttl = null;

    public function __construct(protected Repository $repository, string $namespace, protected Cacheable $cacheable)
    {
    }

    public function hits(string $feature): bool
    {
        return $this->repository->has($this->cacheable->generateKey($feature));
    }

    public function result(string $feature): bool
    {
        return $this->repository->get($this->cacheable->generateKey($feature));
    }

    public function store(string $feature, ?bool $result): void
    {
        $this->repository->put($this->cacheable->generateKey($feature), $result, $this->ttl);
    }

    public function delete(string $feature): void
    {
        $this->repository->delete($this->cacheable->generateKey($feature));
    }

    public function configureTtl(?int $seconds): self
    {
        $this->ttl = $seconds;
        return $this;
    }
}
