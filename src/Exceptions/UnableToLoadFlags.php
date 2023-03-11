<?php

namespace YlsIdeas\FeatureFlags\Exceptions;

final class UnableToLoadFlags extends \RuntimeException
{
    public function __construct(string $message, protected string $path, int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
