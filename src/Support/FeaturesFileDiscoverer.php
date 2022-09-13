<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Support\FeaturesFileDiscovererTest
 */
class FeaturesFileDiscoverer
{
    public function __construct(protected Application $application, protected string $file)
    {
    }

    public function find(): string
    {
        $files = $this->application->make('files');

        if (Str::startsWith($this->file, '/') && $files->exists($this->file)) {
            return $this->file;
        }

        if ($files->exists($path = $this->application->basePath($this->file))) {
            return $path;
        }

        if ($files->exists($path = $this->application->basePath($this->file . '.dist'))) {
            return $path;
        }

        throw new \RuntimeException(sprintf('Not `%s` file could be found.', $this->file));
    }
}
