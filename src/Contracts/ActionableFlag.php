<?php

namespace YlsIdeas\FeatureFlags\Contracts;

interface ActionableFlag
{
    public function feature(): string;
    public function setResult(bool $value): void;
    public function hasResult(): bool;
    public function getResult(): ?bool;
}
