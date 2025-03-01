<?php

namespace YlsIdeas\FeatureFlags\Tests\Gateways;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Guard;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Gateways\GateGateway;

class GateGatewayTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_can_be_initialised(): void
    {
        $guard = Mockery::mock(Guard::class);
        $gate = Mockery::mock(Gate::class);

        $gateway = new GateGateway(
            $guard,
            $gate,
            'access.gate'
        );

        $this->assertInstanceOf(GateGateway::class, $gateway);
    }

    public function test_it_returns_true_if_features_are_accessible(): void
    {
        $guard = Mockery::mock(Guard::class);
        $gate = Mockery::mock(Gate::class);
        $user = Mockery::mock();

        $guard->shouldReceive('user')
            ->withNoArgs()
            ->andReturn($user);

        $gate->shouldReceive('forUser')
            ->with($user)
            ->andReturn($gate);

        $gate->shouldReceive('allows')
            ->with('access.gate', ['my-feature'])
            ->andReturn(true);

        $gateway = new GateGateway(
            $guard,
            $gate,
            'access.gate'
        );

        $this->assertTrue($gateway->accessible('my-feature'));
    }

    public function test_it_returns_false_if_features_are_not_accessible(): void
    {
        $guard = Mockery::mock(Guard::class);
        $gate = Mockery::mock(Gate::class);
        $user = Mockery::mock();

        $guard->shouldReceive('user')
            ->withNoArgs()
            ->andReturn($user);

        $gate->shouldReceive('forUser')
            ->with($user)
            ->andReturn($gate);

        $gate->shouldReceive('allows')
            ->with('access.gate', ['my-feature'])
            ->andReturn(false);

        $gateway = new GateGateway(
            $guard,
            $gate,
            'access.gate'
        );

        $this->assertFalse($gateway->accessible('my-feature'));
    }
}
