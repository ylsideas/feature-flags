<?php

namespace YlsIdeas\FeatureFlags\Tests\Gateways;

use Illuminate\Redis\Connections\Connection;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Gateways\RedisGateway;

class RedisGatewayTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testItCanBeInitialised()
    {
        $connection = \Mockery::mock(Connection::class);

        $gateway = new RedisGateway($connection);

        $this->assertInstanceOf(RedisGateway::class, $gateway);
    }

    public function testItReturnsTrueIfFeaturesAreAccessible()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(true);

        $gateway = new RedisGateway($connection);

        $this->assertTrue($gateway->accessible('my-feature'));
    }

    public function testItReturnsFalseIfFeaturesAreNotAccessible()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(false);

        $gateway = new RedisGateway($connection);

        $this->assertFalse($gateway->accessible('my-feature'));
    }

    public function testItReturnsNullIfFeaturesAreNotDefined()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(null);

        $gateway = new RedisGateway($connection);

        $this->assertNull($gateway->accessible('my-feature'));
    }

    public function testItCanStoreTheStateOfFeaturesSwitchedOn()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('set')
            ->with('features:my-feature', true)
            ->once();

        $gateway = new RedisGateway($connection);

        $gateway->turnOn('my-feature');
    }

    public function testItCanStoreTheStateOfFeaturesSwitchedOff()
    {
        $connection = \Mockery::mock(Connection::class);

        $connection->shouldReceive('set')
            ->with('features:my-feature', false)
            ->once();

        $gateway = new RedisGateway($connection);

        $gateway->turnOff('my-feature');
    }
}
