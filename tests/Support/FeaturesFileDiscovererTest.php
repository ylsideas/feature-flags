<?php

namespace YlsIdeas\FeatureFlags\Tests\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Support\FeaturesFileDiscoverer;

class FeaturesFileDiscovererTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_discovers_absolute_files(): void
    {
        $app = \Mockery::mock(Application::class);
        $files = \Mockery::mock(Filesystem::class);
        $app->shouldReceive('make')
            ->with('files')
            ->andReturn($files);

        $files->shouldReceive('exists')
            ->with('/tmp/test.php')
            ->once()
            ->andReturn(true);

        $discoverer = new FeaturesFileDiscoverer($app, '/tmp/test.php');

        $this->assertSame('/tmp/test.php', $discoverer->find());
    }

    public function test_it_discovers_relative_files(): void
    {
        $app = \Mockery::mock(Application::class);
        $files = \Mockery::mock(Filesystem::class);
        $app->shouldReceive('make')
            ->with('files')
            ->andReturn($files);

        $app->shouldReceive('basePath')
            ->with('.features.php')
            ->once()
            ->andReturn('/path/to/app/.features.php');

        $files->shouldReceive('exists')
            ->with('/path/to/app/.features.php')
            ->once()
            ->andReturn(true);

        $discoverer = new FeaturesFileDiscoverer($app, '.features.php');

        $this->assertSame('/path/to/app/.features.php', $discoverer->find());
    }

    public function test_it_discovers_relative_dist_files(): void
    {
        $app = \Mockery::mock(Application::class);
        $files = \Mockery::mock(Filesystem::class);
        $app->shouldReceive('make')
            ->with('files')
            ->andReturn($files);

        $app->shouldReceive('basePath')
            ->with('.features.php')
            ->once()
            ->andReturn('/path/to/app/.features.php');

        $app->shouldReceive('basePath')
            ->with('.features.php.dist')
            ->once()
            ->andReturn('/path/to/app/.features.php.dist');

        $files->shouldReceive('exists')
            ->with('/path/to/app/.features.php')
            ->once()
            ->andReturn(false);

        $files->shouldReceive('exists')
            ->with('/path/to/app/.features.php.dist')
            ->once()
            ->andReturn(true);

        $discoverer = new FeaturesFileDiscoverer($app, '.features.php');

        $this->assertSame('/path/to/app/.features.php.dist', $discoverer->find());
    }

    public function test_it_throws_an_exception_if_no_file_is_discovered(): void
    {
        $app = \Mockery::mock(Application::class);
        $files = \Mockery::mock(Filesystem::class);
        $app->shouldReceive('make')
            ->with('files')
            ->andReturn($files);

        $app->shouldReceive('basePath')
            ->with('.features.php')
            ->once()
            ->andReturn('/path/to/app/.features.php');

        $app->shouldReceive('basePath')
            ->with('.features.php.dist')
            ->once()
            ->andReturn('/path/to/app/.features.php.dist');

        $files->shouldReceive('exists')
            ->with('/path/to/app/.features.php')
            ->once()
            ->andReturn(false);

        $files->shouldReceive('exists')
            ->with('/path/to/app/.features.php.dist')
            ->once()
            ->andReturn(false);

        $discoverer = new FeaturesFileDiscoverer($app, '.features.php');

        $this->expectException(\RuntimeException::class);

        $discoverer->find();
    }
}
