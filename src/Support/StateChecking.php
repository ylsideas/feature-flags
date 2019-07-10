<?php

namespace YlsIdeas\FeatureFlags\Support;

trait StateChecking
{
    protected function check($state)
    {
        return $state !== 'off' || true;
    }
}
