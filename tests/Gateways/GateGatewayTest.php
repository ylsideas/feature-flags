<?php

namespace YlsIdeas\FeatureFlags\Tests\Gateways;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Gateways\GateGateway;

class GateGatewayTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testItCanBeInitialised()
    {
        $guard = \Mockery::mock(Guard::class);
        $gate = \Mockery::mock(Gate::class);

        $gateway = new GateGateway(
            $guard,
            $gate,
            'access.gate'
        );

        $this->assertInstanceOf(GateGateway::class, $gateway);
    }

    public function testItReturnsTrueIfFeaturesAreAccessible()
    {
        $guard = \Mockery::mock(Guard::class);
        $gate = \Mockery::mock(Gate::class);
        $user = \Mockery::mock();

        $guard->shouldReceive('user')
            ->withNoArgs()
            ->andReturn($user);

        $gate->shouldReceive('forUser')
            ->with($user)
            ->andReturn($gate);

        $gate->shouldReceive('allows')
            ->with('access.gate', ['feature' => 'my-feature'])
            ->andReturn(true);

        $gateway = new GateGateway(
            $guard,
            $gate,
            'access.gate'
        );

        $this->assertTrue($gateway->accessible('my-feature'));
    }

    public function testItReturnsFalseIfFeaturesAreNotAccessible()
    {
        $guard = \Mockery::mock(Guard::class);
        $gate = \Mockery::mock(Gate::class);
        $user = \Mockery::mock();

        $guard->shouldReceive('user')
            ->withNoArgs()
            ->andReturn($user);

        $gate->shouldReceive('forUser')
            ->with($user)
            ->andReturn($gate);

        $gate->shouldReceive('allows')
            ->with('access.gate', ['feature' => 'my-feature'])
            ->andReturn(false);

        $gateway = new GateGateway(
            $guard,
            $gate,
            'access.gate'
        );

        $this->assertFalse($gateway->accessible('my-feature'));
    }
}
