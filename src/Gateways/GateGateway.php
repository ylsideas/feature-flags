<?php

namespace YlsIdeas\FeatureFlags\Gateways;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use YlsIdeas\FeatureFlags\Contracts\Cacheable;
use YlsIdeas\FeatureFlags\Contracts\Gateway;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Gateways\GateGatewayTest
 */
class GateGateway implements Gateway, Cacheable
{
    public function __construct(
        protected Guard $guard,
        protected Gate $gate,
        protected string $gateName
    ) {
    }

    public function accessible(string $feature): ?bool
    {
        return $this->gate->forUser($this->guard->user())->allows($this->gateName, [$feature]);
    }

    public function generateKey(string $feature): string
    {
        /** @var Model|null $model */
        $model = $this->guard->user();

        if (is_null($model)) {
            return md5($feature);
        }

        return implode(':', [md5($feature), $model::class, $model->getKey()]);
    }
}
