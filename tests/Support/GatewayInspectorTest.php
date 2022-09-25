<?php

namespace YlsIdeas\FeatureFlags\Tests\Support;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\ActionableFlag;
use YlsIdeas\FeatureFlags\Contracts\DebuggableFlag;
use YlsIdeas\FeatureFlags\Contracts\Gateway;
use YlsIdeas\FeatureFlags\Support\ActionDebugLog;
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
            'test',
            $gateway
        );

        $this->assertInstanceOf(GatewayInspector::class, $inspector);
    }

    public function test_it_will_skip_to_the_next_inspector_when_a_result_exists(): void
    {
        $gateway = \Mockery::mock(Gateway::class);

        $inspector = new GatewayInspector(
            'test',
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
            'test',
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
            'test',
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
            'test',
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
            'test',
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
            'test',
            $gateway
        );

        $gateway->shouldReceive('accessible')
            ->with($action->feature)
            ->andReturn(false);

        $result = $inspector->handle($action, fn (ActionableFlag $flag) => $flag);

        $this->assertSame($action, $result);
        $this->assertFalse($action->getResult());
    }

    /**
     * @dataProvider debugScenarios
     */
    public function test_it_provides_debug_information(callable $constructs, callable $assert, ?bool $result = null)
    {
        $action = new ActionableFlag();
        if ($result) {
            $action->result = $result;
        }
        $action->feature = 'my-feature';
        $action->debug = new ActionDebugLog('test.php', 1);

        $inspector = new GatewayInspector(
            'test',
            ...($constructs($action->feature))
        );

        $result = $inspector->handle($action, fn (ActionableFlag $flag) => $flag);

        $this->assertInstanceOf(DebuggableFlag::class, $result);

        $assert($result->log());
    }

    public function debugScenarios(): \Generator
    {
        yield 'found result' => [
            fn (string $feature) => [
                'gateway' => \Mockery::mock(Gateway::class)->shouldReceive('accessible')
                    ->with($feature)
                    ->andReturn(true)
                    ->getMock(),
            ],
            function (ActionDebugLog $log) {
                $this->assertSame([
                    [
                        'pipe' => 'test',
                        'reason' => ActionDebugLog::REASON_RESULT,
                        'result' => true,
                    ],
                ], $log->decisions);
            },
        ];
        yield 'cached result' => [
            fn (string $feature) => [
                'gateway' => \Mockery::mock(Gateway::class),
                'cache' => \Mockery::mock(GatewayCache::class)
                    ->shouldReceive('hits')
                    ->with($feature)
                    ->andReturn(true)
                    ->getMock()
                    ->shouldReceive('result')
                    ->with($feature)
                    ->andReturn(true)
                    ->getMock(),
            ],
            function (ActionDebugLog $log) {
                $this->assertSame([
                    [
                        'pipe' => 'test',
                        'reason' => ActionDebugLog::REASON_CACHE,
                        'result' => true,
                    ],
                ], $log->decisions);
            },
        ];
        yield 'filter failed' => [
            fn (string $feature) => [
                'gateway' => \Mockery::mock(Gateway::class),
                'filter' => \Mockery::mock(FeatureFilter::class)
                    ->shouldReceive('fails')
                    ->with($feature)
                    ->andReturn(true)
                    ->getMock(),
            ],
            function (ActionDebugLog $log) {
                $this->assertSame([
                    [
                        'pipe' => 'test',
                        'reason' => ActionDebugLog::REASON_FILTER,
                        'result' => null,
                    ],
                ], $log->decisions);
            },
        ];
        yield 'already has result' => [
            fn (string $feature) => [
                'gateway' => \Mockery::mock(Gateway::class),
            ],
            function (ActionDebugLog $log) {
                $this->assertSame([
                    [
                        'pipe' => 'test',
                        'reason' => ActionDebugLog::REASON_RESULT_ALREADY_FOUND,
                        'result' => true,
                    ],
                ], $log->decisions);
            },
            true,
        ];
        yield 'no result' => [
            fn (string $feature) => [
                'gateway' => \Mockery::mock(Gateway::class)->shouldReceive('accessible')
                    ->with($feature)
                    ->andReturn(null)
                    ->getMock(),
            ],
            function (ActionDebugLog $log) {
                $this->assertSame([
                    [
                        'pipe' => 'test',
                        'reason' => ActionDebugLog::REASON_NO_RESULT,
                        'result' => null,
                    ],
                ], $log->decisions);
            },
        ];
    }
}
