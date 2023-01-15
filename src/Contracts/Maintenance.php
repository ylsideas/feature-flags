<?php

namespace YlsIdeas\FeatureFlags\Contracts;

interface Maintenance
{
    public function active(): bool;

    public function parameters(): ?array;

    public function callActivation(array $properties): void;

    public function callDeactivation(): void;
}
