<?php

namespace YlsIdeas\FeatureFlags\Tests\Gateways;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Gateways\DatabaseGateway;

class DatabaseGatewayTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testItCanBeInitialised()
    {
        $connection = \Mockery::mock(Connection::class);

        $gateway = new DatabaseGateway(
            $connection
        );

        $this->assertInstanceOf(DatabaseGateway::class, $gateway);
    }

    public function testItReturnsTrueIfFeaturesAreAccessible()
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

        $gateway = new DatabaseGateway($connection);

        $this->assertTrue($gateway->accessible('my-feature'));
    }

    public function testItReturnsFalseIfFeaturesAreNotAccessible()
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

        $gateway = new DatabaseGateway($connection);

        $this->assertFalse($gateway->accessible('my-feature'));
    }

    public function testItReturnsNullIfFeaturesAreNotDefined()
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

        $gateway = new DatabaseGateway($connection);

        $this->assertNull($gateway->accessible('my-feature'));
    }

    public function testItCanStoreTheStateOfFeaturesSwitchedOn()
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

        $gateway = new DatabaseGateway($connection);

        $gateway->turnOn('my-feature');
    }

    public function testItCanStoreTheStateOfFeaturesSwitchedOff()
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

        $gateway = new DatabaseGateway($connection);

        $gateway->turnOff('my-feature');
    }
}
