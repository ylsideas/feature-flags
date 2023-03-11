<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Contracts\Container\Container;
use YlsIdeas\FeatureFlags\Contracts\InMemoryLoader;
use YlsIdeas\FeatureFlags\Exceptions\UnableToLoadFlags;

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
            throw new UnableToLoadFlags(sprintf('File `%s` does not return a callable', $file), $file);
        }

        return $this->container->call($callable);
    }
}
