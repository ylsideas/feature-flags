<?php

namespace YlsIdeas\FeatureFlags\Contracts;

interface Toggleable
{
    public function turnOn(string $feature): void;
    public function turnOff(string $feature): void;
}
