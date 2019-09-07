<?php

namespace YlsIdeas\FeatureFlags\Tests\Middlewares;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Application;
use YlsIdeas\FeatureFlags\Contracts\Repository;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use YlsIdeas\FeatureFlags\Middleware\FeatureFlagState;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FeatureFlagStateTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function itCanAbortRequestsWhenFeaturesAreNotAccessible()
    {
        $exception = new HttpException(403);

        $this->expectExceptionObject($exception);
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(403)
            ->once()
            ->andThrow($exception);

        $repository = \Mockery::mock(Repository::class);

        $repository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(false);

        $request = new Request();

        $middleware = new FeatureFlagState($repository, $app);

        $middleware->handle($request, function () {
        }, 'my-feature', 'on');
    }

    /** @test */
    public function itCanAbortRequestsWhenFeaturesAreNotAccessibleAndExpectingToBeOff()
    {
        $exception = new HttpException(404);

        $this->expectExceptionObject($exception);
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(403)
            ->once()
            ->andThrow($exception);

        $repository = \Mockery::mock(Repository::class);

        $repository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(true);

        $request = new Request();

        $middleware = new FeatureFlagState($repository, $app);

        $middleware->handle($request, function () {
        }, 'my-feature', 'off');
    }

    /** @test */
    public function itContinuesTheChainIfFeaturesAreAccessible()
    {
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(403)
            ->never();

        $repository = \Mockery::mock(Repository::class);

        $repository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(true);

        $expectedRequest = new Request();

        $middleware = new FeatureFlagState($repository, $app);

        $this->assertTrue($middleware->handle(
            $expectedRequest,
            function ($request) use ($expectedRequest) {
                $this->assertSame($expectedRequest, $request);

                return true;
            },
            'my-feature'
        ));
    }

    /** @test */
    public function itCanAbortRequestsWithASpecifiedHttpStatusCode()
    {
        $exception = new HttpException(404);

        $this->expectExceptionObject($exception);
        $app = \Mockery::mock(Application::class);

        $app->shouldReceive('abort')
            ->with(404)
            ->once()
            ->andThrow($exception);

        $repository = \Mockery::mock(Repository::class);

        $repository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(false);

        $request = new Request();

        $middleware = new FeatureFlagState($repository, $app);

        $middleware->handle($request, function () {
        }, 'my-feature', 'on', 404);
    }
}
