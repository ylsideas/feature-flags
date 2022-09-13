<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Str;

class FeaturesFileDiscoverer
{
    public function __construct(protected Application $application, protected string $file)
    {
    }

    public function find(): string
    {
        if (Str::startsWith($this->file, '/') && file_exists($this->file)) {
            return $this->file;
        }

        if (file_exists($path = $this->application->basePath($this->file))) {
            return $path;
        }

        if (file_exists($path = $this->application->basePath($this->file . '.dist'))) {
            return $this->application->basePath($this->file);
        }
    }
}
