<?php

namespace YlsIdeas\FeatureFlags\Gateways;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use YlsIdeas\FeatureFlags\Contracts\Cacheable;
use YlsIdeas\FeatureFlags\Contracts\Toggleable;
use YlsIdeas\FeatureFlags\Contracts\Gateway;

class DatabaseGateway implements Gateway, Toggleable, Cacheable
{
    protected Connection $connection;
    protected string $table;
    /**
     * @var string
     */
    protected $field;

    public function __construct(Connection $connection, string $table = 'features', string $field = 'active_at')
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->field = $field;
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
