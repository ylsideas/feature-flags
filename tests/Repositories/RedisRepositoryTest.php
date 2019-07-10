<?php

namespace YlsIdeas\FeatureFlags\Tests\Repositories;

use PHPUnit\Framework\TestCase;
use Illuminate\Redis\Connections\Connection;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use YlsIdeas\FeatureFlags\Repositories\RedisRepository;

class RedisRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function itCanBeInitialised()
    {
        $connection = \Mockery::mock(Connection::class);

        $repository = new RedisRepository($connection);

        $this->assertInstanceOf(RedisRepository::class, $repository);
    }

    /** @test */
    public function itReturnsTrueIfFeaturesAreAccessible()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(true);

        $repository = new RedisRepository($connection);

        $this->assertTrue($repository->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsFalseIfFeaturesAreNotAccessible()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(false);

        $repository = new RedisRepository($connection);

        $this->assertFalse($repository->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsNullIfFeaturesAreNotDefined()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(null);

        $repository = new RedisRepository($connection);

        $this->assertNull($repository->accessible('my-feature'));
    }

    /** @test */
    public function itCanFetchAllTheFeaturesAndTheirCurrentState()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('keys')
            ->with('features:*')
            ->once()
            ->andReturn(['features:my-feature']);

        $connection->shouldReceive('mget')
            ->with(['features:my-feature'])
            ->once()
            ->andReturn([true]);

        $repository = new RedisRepository($connection);

        $this->assertSame(
            [
                'my-feature' => true,
            ],
            $repository->all()
        );
    }

    /** @test */
    public function itCanStoreTheStateOfFeaturesSwitchedOn()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('set')
            ->with('features:my-feature', true)
            ->once();

        $repository = new RedisRepository($connection);

        $repository->turnOn('my-feature');
    }

    /** @test */
    public function itCanStoreTheStateOfFeaturesSwitchedOff()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('set')
            ->with('features:my-feature', false)
            ->once();

        $repository = new RedisRepository($connection);

        $repository->turnOff('my-feature');
    }
}
