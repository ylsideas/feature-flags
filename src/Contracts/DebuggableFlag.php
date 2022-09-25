<?php

namespace YlsIdeas\FeatureFlags\Contracts;

use YlsIdeas\FeatureFlags\Support\ActionDebugLog;

interface DebuggableFlag extends ActionableFlag
{
    public function isDebuggable(): bool;

    public function storeInspectionInformation(string $pipe, string $reason, ?bool $result = null);

    public function log(): ?ActionDebugLog;
}
