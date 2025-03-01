<?php

namespace YlsIdeas\FeatureFlags\Tests\Gateways;

use Illuminate\Redis\Connections\Connection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Gateways\RedisGateway;

class RedisGatewayTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_can_be_initialised(): void
    {
        $connection = Mockery::mock(Connection::class);

        $gateway = new RedisGateway($connection);

        $this->assertInstanceOf(RedisGateway::class, $gateway);
    }

    public function test_it_returns_true_if_features_are_accessible(): void
    {
        $connection = Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(true);

        $gateway = new RedisGateway($connection);

        $this->assertTrue($gateway->accessible('my-feature'));
    }

    public function test_it_returns_false_if_features_are_not_accessible(): void
    {
        $connection = Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(false);

        $gateway = new RedisGateway($connection);

        $this->assertFalse($gateway->accessible('my-feature'));
    }

    public function test_it_returns_null_if_features_are_not_defined(): void
    {
        $connection = Mockery::mock(Connection::class);

        $connection->shouldReceive('get')
            ->with('features:my-feature')
            ->once()
            ->andReturn(null);

        $gateway = new RedisGateway($connection);

        $this->assertNull($gateway->accessible('my-feature'));
    }

    public function test_it_can_store_the_state_of_features_switched_on(): void
    {
        $connection = Mockery::mock(Connection::class);

        $connection->shouldReceive('set')
            ->with('features:my-feature', true)
            ->once();

        $gateway = new RedisGateway($connection);

        $gateway->turnOn('my-feature');
    }

    public function test_it_can_store_the_state_of_features_switched_off(): void
    {
        $connection = Mockery::mock(Connection::class);

        $connection->shouldReceive('set')
            ->with('features:my-feature', false)
            ->once();

        $gateway = new RedisGateway($connection);

        $gateway->turnOff('my-feature');
    }
}
