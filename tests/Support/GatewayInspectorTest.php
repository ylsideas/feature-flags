<?php

namespace YlsIdeas\FeatureFlags\Tests\Support;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\ActionableFlag;
use YlsIdeas\FeatureFlags\Contracts\Gateway;
use YlsIdeas\FeatureFlags\Support\FeatureFilter;
use YlsIdeas\FeatureFlags\Support\GatewayCache;
use YlsIdeas\FeatureFlags\Support\GatewayInspector;

class GatewayInspectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_can_be_initialised(): void
    {
        $gateway = \Mockery::mock(Gateway::class);

        $inspector = new GatewayInspector(
            $gateway
        );

        $this->assertInstanceOf(GatewayInspector::class, $inspector);
    }

    public function test_it_will_skip_to_the_next_inspector_when_a_result_exists(): void
    {
        $gateway = \Mockery::mock(Gateway::class);

        $inspector = new GatewayInspector(
            $gateway
        );
        $action = new ActionableFlag();
        $action->result = true;

        $gateway->shouldNotReceive('accessible');

        $result = $inspector->handle($action, fn (ActionableFlag $flag) => $flag);

        $this->assertSame($action, $result);
    }

    public function test_it_filter_features_while_inspecting(): void
    {
        $gateway = \Mockery::mock(Gateway::class);
        $action = new ActionableFlag();
        $action->feature = 'my-feature';

        $inspector = new GatewayInspector(
            $gateway,
            filter: \Mockery::mock(FeatureFilter::class)
                ->shouldReceive('fails')
                ->with($action->feature)
                ->andReturn(true)
                ->getMock()
        );

        $gateway->shouldNotReceive('accessible');

        $result = $inspector->handle($action, fn (ActionableFlag $flag) => $flag);

        $this->assertSame($action, $result);
    }

    public function test_it_uses_configured_caches_for_results()
    {
        $gateway = \Mockery::mock(Gateway::class);
        $action = new ActionableFlag();
        $action->feature = 'my-feature';

        $inspector = new GatewayInspector(
            $gateway,
            cache: \Mockery::mock(GatewayCache::class)
                ->shouldReceive('hits')
                ->with($action->feature)
                ->andReturn(true)
                ->getMock()
                ->shouldReceive('result')
                ->with($action->feature)
                ->andReturn(true)
                ->getMock()
        );

        $gateway->shouldNotReceive('accessible');

        $result = $inspector->handle($action, fn (ActionableFlag $flag) => $flag);

        $this->assertSame($action, $result);
        $this->assertTrue($action->getResult());
    }

    public function test_it_uses_configured_caches(): void
    {
        $gateway = \Mockery::mock(Gateway::class);
        $action = new ActionableFlag();
        $action->feature = 'my-feature';

        $inspector = new GatewayInspector(
            $gateway,
            cache: \Mockery::mock(GatewayCache::class)
                ->shouldReceive('hits')
                ->with($action->feature)
                ->andReturn(false)
                ->getMock()
                ->shouldNotReceive('result')
                ->getMock()
        );

        $gateway->shouldReceive('accessible')
            ->with($action->feature)
            ->andReturn(null);

        $result = $inspector->handle($action, fn (ActionableFlag $flag) => $flag);

        $this->assertSame($action, $result);
        $this->assertNull($action->getResult());
    }

    public function test_it_stores_results_in_caches(): void
    {
        $gateway = \Mockery::mock(Gateway::class);
        $action = new ActionableFlag();
        $action->feature = 'my-feature';

        $inspector = new GatewayInspector(
            $gateway,
            cache: \Mockery::mock(GatewayCache::class)
                ->shouldReceive('hits')
                ->with($action->feature)
                ->andReturn(false)
                ->getMock()
                ->shouldNotReceive('result')
                ->getMock()
                ->shouldReceive('store')
                ->with($action->feature, true)
                ->getMock()
        );

        $gateway->shouldReceive('accessible')
            ->with($action->feature)
            ->andReturn(true);

        $result = $inspector->handle($action, fn (ActionableFlag $flag) => $flag);

        $this->assertSame($action, $result);
        $this->assertTrue($action->getResult());
    }

    public function test_it_sets_results_in_the_action(): void
    {
        $gateway = \Mockery::mock(Gateway::class);
        $action = new ActionableFlag();
        $action->feature = 'my-feature';

        $inspector = new GatewayInspector(
            $gateway
        );

        $gateway->shouldReceive('accessible')
            ->with($action->feature)
            ->andReturn(false);

        $result = $inspector->handle($action, fn (ActionableFlag $flag) => $flag);

        $this->assertSame($action, $result);
        $this->assertFalse($action->getResult());
    }
}
