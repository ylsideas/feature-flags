<?php

namespace YlsIdeas\FeatureFlags\Exceptions;

class FeatureExpired extends \RuntimeException
{
    public function __construct(
        protected string $feature,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    protected function feature(): string
    {
        return $this->feature;
    }
}
