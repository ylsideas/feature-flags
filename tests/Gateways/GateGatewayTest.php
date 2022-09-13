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

    /** @test */
    public function itCanBeInitialised()
    {
        $guard = \Mockery::mock(Guard::class);
        $gate = \Mockery::mock(Gate::class);

        $gateway = new GateGateway(
            $guard, $gate, 'access.gate'
        );

        $this->assertInstanceOf(GateGateway::class, $gateway);
    }

    /** @test */
    public function itReturnsTrueIfFeaturesAreAccessible()
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
            $guard, $gate, 'access.gate'
        );

        $this->assertTrue($gateway->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsFalseIfFeaturesAreNotAccessible()
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
            $guard, $gate, 'access.gate'
        );

        $this->assertFalse($gateway->accessible('my-feature'));
    }
}
