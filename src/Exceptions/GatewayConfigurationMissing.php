<?php

namespace YlsIdeas\FeatureFlags\Exceptions;

class GatewayConfigurationMissing extends \RuntimeException
{
    public function __construct(string $message, protected string $gateway, int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function getGateway(): string
    {
        return $this->gateway;
    }
}
