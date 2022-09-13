<?php

namespace YlsIdeas\FeatureFlags\Tests\Gateways;

use Illuminate\Redis\Connections\Connection;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Gateways\RedisGateway;

class RedisGatewayTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function itCanBeInitialised()
    {
        $connection = \Mockery::mock(Connection::class);

        $gateway = new RedisGateway($connection);

        $this->assertInstanceOf(RedisGateway::class, $gateway);
    }

    /** @test */
    public function itReturnsTrueIfFeaturesAreAccessible()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(true);

        $gateway = new RedisGateway($connection);

        $this->assertTrue($gateway->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsFalseIfFeaturesAreNotAccessible()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(false);

        $gateway = new RedisGateway($connection);

        $this->assertFalse($gateway->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsNullIfFeaturesAreNotDefined()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(null);

        $gateway = new RedisGateway($connection);

        $this->assertNull($gateway->accessible('my-feature'));
    }

    /** @test */
    public function itCanStoreTheStateOfFeaturesSwitchedOn()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('set')
            ->with('features:my-feature', true)
            ->once();

        $gateway = new RedisGateway($connection);

        $gateway->turnOn('my-feature');
    }

    /** @test */
    public function itCanStoreTheStateOfFeaturesSwitchedOff()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('set')
            ->with('features:my-feature', false)
            ->once();

        $gateway = new RedisGateway($connection);

        $gateway->turnOff('my-feature');
    }
}
