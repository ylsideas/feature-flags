<?php

namespace YlsIdeas\FeatureFlags\Tests\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Repositories\DatabaseRepository;

class DatabaseRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function itCanBeInitialised()
    {
        $connection = \Mockery::mock(Connection::class);

        $repository = new DatabaseRepository(
            $connection
        );

        $this->assertInstanceOf(DatabaseRepository::class, $repository);
    }

    /** @test */
    public function itReturnsTrueIfFeaturesAreAccessible()
    {
        $connection = \Mockery::mock(Connection::class);
        $query = \Mockery::mock(Builder::class);

        $connection->shouldReceive('table')
            ->with('features')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('where')
            ->with('feature', 'my-feature')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('first')
            ->once()
            ->andReturn((object) [
                'active_at' => now(),
            ]);

        $repository = new DatabaseRepository($connection);

        $this->assertTrue($repository->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsFalseIfFeaturesAreNotAccessible()
    {
        $connection = \Mockery::mock(Connection::class);
        $query = \Mockery::mock(Builder::class);

        $connection->shouldReceive('table')
            ->with('features')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('where')
            ->with('feature', 'my-feature')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('first')
            ->once()
            ->andReturn((object) [
                'active_at' => null,
            ]);

        $repository = new DatabaseRepository($connection);

        $this->assertFalse($repository->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsNullIfFeaturesAreNotDefined()
    {
        $connection = \Mockery::mock(Connection::class);
        $query = \Mockery::mock(Builder::class);

        $connection->shouldReceive('table')
            ->with('features')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('where')
            ->with('feature', 'my-feature')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $repository = new DatabaseRepository($connection);

        $this->assertNull($repository->accessible('my-feature'));
    }

    /** @test */
    public function itCanFetchAllTheFeaturesAndTheirCurrentState()
    {
        $connection = \Mockery::mock(Connection::class);
        $query = \Mockery::mock(Builder::class);

        $connection->shouldReceive('table')
            ->with('features')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('get')
            ->with(['feature', 'active_at'])
            ->once()
            ->andReturn(collect([
                (object) [
                    'feature' => 'my-feature',
                    'active_at' => now(),
                ],
                (object) [
                    'feature' => 'my-second-feature',
                    'active_at' => null,
                ],
            ]));

        $repository = new DatabaseRepository($connection);

        $this->assertSame(
            [
                'my-feature' => true,
                'my-second-feature' => false,
            ],
            $repository->all()
        );
    }

    /** @test */
    public function itCanStoreTheStateOfFeaturesSwitchedOn()
    {
        $connection = \Mockery::mock(Connection::class);
        $query = \Mockery::mock(Builder::class);

        $connection->shouldReceive('table')
            ->with('features')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('updateOrInsert')
            ->withArgs(function ($find, $data) {
                $this->assertArrayHasKey('feature', $find);
                $this->assertArrayHasKey('active_at', $data);

                $this->assertSame('my-feature', $find['feature']);
                $this->assertInstanceOf(Carbon::class, $data['active_at']);

                return true;
            })
            ->once();

        $repository = new DatabaseRepository($connection);

        $repository->turnOn('my-feature');
    }

    /** @test */
    public function itCanStoreTheStateOfFeaturesSwitchedOff()
    {
        $connection = \Mockery::mock(Connection::class);
        $query = \Mockery::mock(Builder::class);

        $connection->shouldReceive('table')
            ->with('features')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('updateOrInsert')
            ->with([
                'feature' => 'my-feature',
            ], [
                'active_at' => null,
            ])
            ->once();

        $repository = new DatabaseRepository($connection);

        $repository->turnOff('my-feature');
    }
}
