<?php

namespace YlsIdeas\FeatureFlags\Tests\Support;

use Illuminate\Contracts\Container\Container;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Exceptions\UnableToLoadFlags;
use YlsIdeas\FeatureFlags\Support\FeaturesFileDiscoverer;
use YlsIdeas\FeatureFlags\Support\FileLoader;

class FileLoaderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_loads_features_from_php_file(): void
    {
        $discoverer = \Mockery::mock(FeaturesFileDiscoverer::class);
        $container = \Mockery::mock(Container::class);

        $features = [
            'my-feature' => true,
        ];

        $discoverer->shouldReceive('find')
            ->once()
            ->andReturn(__DIR__ . '/../fixtures/features.php');

        $container->shouldReceive('call')
            ->with(\Mockery::on(fn ($callable) => is_callable($callable)))
            ->once()
            ->andReturn($features);

        $loader = new FileLoader($discoverer, $container);

        $results = $loader->load();

        $this->assertSame($features, $results);
    }

    public function test_it_throws_an_exception_if_a_callable_isnt_returned(): void
    {
        $discoverer = \Mockery::mock(FeaturesFileDiscoverer::class);
        $container = \Mockery::mock(Container::class);

        $features = [
            'my-feature' => true,
        ];

        $discoverer->shouldReceive('find')
            ->once()
            ->andReturn(__DIR__ . '/../fixtures/features-non-callable.php');

        $loader = new FileLoader($discoverer, $container);

        $this->expectException(UnableToLoadFlags::class);
        $this->expectExceptionMessage(
            'File `' .
            __DIR__ . '/../fixtures/features-non-callable.php' .
            '` does not return a callable'
        );

        $loader->load();
    }
}
