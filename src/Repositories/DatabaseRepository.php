<?php

namespace YlsIdeas\FeatureFlags\Repositories;

use Illuminate\Database\Connection;
use YlsIdeas\FeatureFlags\Contracts\Repository;

class DatabaseRepository implements Repository
{
    /**
     * @var Connection
     */
    protected $connection;
    protected $table;
    /**
     * @var string
     */
    protected $field;

    public function __construct(Connection $connection, $table = 'features', $field = 'active_at')
    {
        $this->connection = $connection;
        $this->table = $table;
        $this->field = $field;
    }

    public function accessible(string $feature)
    {
        $row = $this->getTable()->where('feature', $feature)->first();

        if ($row) {
            return (bool) ($row->{$this->field} ?? false);
        }

        return null;
    }

    /**
     * @return array<string, bool>
     */
    public function all()
    {
        return $this->getTable()
            ->get(['feature', 'active_at'])
            ->mapWithKeys(function ($row) {
                return [$row->feature => (bool) $row->{$this->field}];
            })
            ->toArray();
    }

    public function turnOn(string $feature)
    {
        $this->getTable()->updateOrInsert([
            'feature' => $feature,
        ], [
            $this->field => now(),
        ]);
    }

    public function turnOff(string $feature)
    {
        $this->getTable()->updateOrInsert([
            'feature' => $feature,
        ], [
            $this->field => null,
        ]);
    }

    protected function getTable()
    {
        return $this->connection->table($this->table);
    }
}
