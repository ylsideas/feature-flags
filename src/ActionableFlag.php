<?php

namespace YlsIdeas\FeatureFlags;

use YlsIdeas\FeatureFlags\Contracts\ActionableFlag as ActionableFlagContract;

class ActionableFlag implements ActionableFlagContract
{
    public string $feature;
    public ?bool $result = null;

    public function feature(): string
    {
        return $this->feature;
    }

    public function setResult(bool $value): void
    {
        $this->result = $value;
    }

    public function getResult(): ?bool
    {
        return $this->result;
    }

    public function hasResult(): bool
    {
        return ! is_null($this->result);
    }
}
