<?php

namespace YlsIdeas\FeatureFlags\Support;

use InvalidArgumentException;

trait StateChecking
{
    /**
     * Returns true for 'on' and false for 'off'.
     */
    protected function check(string $state): bool
    {
        if ($state === 'on') {
            return true;
        } elseif ($state === 'off') {
            return false;
        }

        throw new InvalidArgumentException('$state parameters is expected to being be `on` or `off`');
    }
}
