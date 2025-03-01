<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Contracts\Container\Container;
use RuntimeException;
use YlsIdeas\FeatureFlags\Contracts\InMemoryLoader;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Support\FileLoaderTest
 */
class FileLoader implements InMemoryLoader
{
    public function __construct(protected FeaturesFileDiscoverer $discoverer, protected Container $container)
    {
    }

    public function load(): array
    {
        $callable = require($file = $this->discoverer->find());
        if (! is_callable($callable)) {
            throw new RuntimeException(sprintf('File `%s` does not return a callable', $file));
        }

        return $this->container->call($callable);
    }
}
