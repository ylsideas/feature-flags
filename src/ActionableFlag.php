<?php

namespace YlsIdeas\FeatureFlags;

use YlsIdeas\FeatureFlags\Contracts\DebuggableFlag as ActionableFlagContract;
use YlsIdeas\FeatureFlags\Support\ActionDebugLog;

class ActionableFlag implements ActionableFlagContract
{
    public string $feature;
    public ?bool $result = null;
    public ?ActionDebugLog $debug = null;

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

    public function isDebuggable(): bool
    {
        return (bool) $this->debug;
    }

    public function storeInspectionInformation(string $pipe, string $reason, ?bool $result = null): void
    {
        $this->debug->addDecision($pipe, $reason, $result);
    }

    public function log(): ?ActionDebugLog
    {
        return $this->debug;
    }
}
