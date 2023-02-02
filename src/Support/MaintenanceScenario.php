<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Contracts\Support\Arrayable;

class MaintenanceScenario implements Arrayable
{
    public string $feature;
    public bool $onEnabled;

    protected array $attributes = [];

    public function whenEnabled(string $feature): static
    {
        $this->feature = $feature;
        $this->onEnabled = true;

        return $this;
    }

    public function whenDisabled(string $feature): static
    {
        $this->feature = $feature;
        $this->onEnabled = false;

        return $this;
    }

    public function refresh(int $seconds): static
    {
        $this->attributes['refresh'] = (string) $seconds;

        return $this;
    }

    public function statusCode(int $status): static
    {
        $this->attributes['status'] = $status;

        return $this;
    }

    public function retry(int $seconds): static
    {
        $this->attributes['retry'] = $seconds;

        return $this;
    }

    public function secret(string $secret): static
    {
        $this->attributes['secret'] = $secret;

        return $this;
    }

    public function redirect(string $url): static
    {
        $this->attributes['redirect'] = $url;

        return $this;
    }

    public function template(string $html): static
    {
        $this->attributes['template'] = $html;

        return $this;
    }

    /**
     * @param string[] $urls
     */
    public function exceptPaths(array $urls): static
    {
        $this->attributes['except'] = $urls;

        return $this;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
