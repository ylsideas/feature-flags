<?php

namespace YlsIdeas\FeatureFlags\Gateways;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use YlsIdeas\FeatureFlags\Contracts\Cacheable;
use YlsIdeas\FeatureFlags\Contracts\Gateway;
use YlsIdeas\FeatureFlags\Contracts\Toggleable;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Gateways\DatabaseGatewayTest
 */
class DatabaseGateway implements Gateway, Toggleable, Cacheable
{
    public function __construct(protected Connection $connection, protected string $table = 'features', protected string $field = 'active_at')
    {
    }

    public function accessible(string $feature): ?bool
    {
        $row = $this->getTable()->where('feature', $feature)->first();

        if ($row) {
            return (bool) ($row->{$this->field} ?? false);
        }

        return null;
    }

    public function turnOn(string $feature): void
    {
        $this->getTable()->updateOrInsert([
            'feature' => $feature,
        ], [
            $this->field => now(),
        ]);
    }

    public function turnOff(string $feature): void
    {
        $this->getTable()->updateOrInsert([
            'feature' => $feature,
        ], [
            $this->field => null,
        ]);
    }

    protected function getTable(): Builder
    {
        return $this->connection->table($this->table);
    }

    public function generateKey(string $feature): string
    {
        return md5($feature);
    }
}
