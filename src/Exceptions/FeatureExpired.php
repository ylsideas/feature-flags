<?php

namespace YlsIdeas\FeatureFlags\Exceptions;

use RuntimeException;
use Throwable;

class FeatureExpired extends RuntimeException
{
    public function __construct(
        protected string $feature,
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    protected function feature(): string
    {
        return $this->feature;
    }
}
