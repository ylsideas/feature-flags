<?php

namespace YlsIdeas\FeatureFlags\Support;

trait StateChecking
{
    /**
     * Returns true for 'on' and false for 'off'.
     *
     * @param string $state
     * @return bool
     */
    protected function check(string $state)
    {
        if ($state === 'on') {
            return true;
        } elseif ($state === 'off') {
            return false;
        }

        throw new \InvalidArgumentException('$state parameters is expected to being be `on` or `off`');
    }
}
