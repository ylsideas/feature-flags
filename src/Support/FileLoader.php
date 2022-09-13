<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Contracts\Container\Container;
use YlsIdeas\FeatureFlags\Contracts\InMemoryLoader;

class FileLoader implements InMemoryLoader
{
    public function __construct(protected FeaturesFileDiscoverer $discoverer, protected Container $container)
    {
    }

    public function load(): array
    {
        $callable = require $this->discoverer->find();
        if (!is_callable($callable)) {
            throw new \RuntimeException(sprintf('File `%s` does not return a callable'));
        }
        return $this->container->call($callable);
    }
}
