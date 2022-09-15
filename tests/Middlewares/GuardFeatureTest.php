<?php

namespace YlsIdeas\FeatureFlags\Tests\Middlewares;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use YlsIdeas\FeatureFlags\Manager;
use YlsIdeas\FeatureFlags\Middlewares\GuardFeature;

class GuardFeatureTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testItCanAbortRequestsWhenFeaturesAreNotAccessible()
    {
        $exception = new HttpException(403);

        $this->expectExceptionObject($exception);
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(403)
            ->once()
            ->andThrow($exception);

        $manager = \Mockery::mock(Manager::class);

        $manager->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(false);

        $request = new Request();

        $middleware = new GuardFeature($manager, $app);

        $middleware->handle($request, function () {
        }, 'my-feature', 'on');
    }

    public function testItCanAbortRequestsWhenFeaturesAreNotAccessibleAndExpectingToBeOff()
    {
        $exception = new HttpException(404);

        $this->expectExceptionObject($exception);
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(403)
            ->once()
            ->andThrow($exception);

        $manager = \Mockery::mock(Manager::class);

        $manager->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(true);

        $request = new Request();

        $middleware = new GuardFeature($manager, $app);

        $middleware->handle($request, function () {
        }, 'my-feature', 'off');
    }

    public function testItContinuesTheChainIfFeaturesAreAccessible()
    {
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(403)
            ->never();

        $manager = \Mockery::mock(Manager::class);

        $manager->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(true);

        $expectedRequest = new Request();

        $middleware = new GuardFeature($manager, $app);

        $this->assertTrue($middleware->handle(
            $expectedRequest,
            function ($request) use ($expectedRequest) {
                $this->assertSame($expectedRequest, $request);

                return true;
            },
            'my-feature'
        ));
    }

    public function testItCanAbortRequestsWithASpecifiedHttpStatusCode()
    {
        $exception = new HttpException(404);

        $this->expectExceptionObject($exception);
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(404)
            ->once()
            ->andThrow($exception);

        $manager = \Mockery::mock(Manager::class);

        $manager->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(false);

        $request = new Request();

        $middleware = new GuardFeature($manager, $app);

        $middleware->handle($request, function () {
        }, 'my-feature', 'on', 404);
    }
}
