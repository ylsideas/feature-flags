<?php

namespace YlsIdeas\FeatureFlags\Tests\Gateways;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Contracts\InMemoryLoader;
use YlsIdeas\FeatureFlags\Gateways\InMemoryGateway;

class InMemoryGatewayTest extends TestCase
{
    /** @test */
    public function itReturnsTrueIfFeaturesAreAccessible()
    {
        $gateway = new InMemoryGateway($this->getLoader([
            'my-feature' => true,
        ]));

        $this->assertTrue($gateway->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsFalseIfFeaturesAreNotAccessible()
    {
        $gateway = new InMemoryGateway($this->getLoader([
            'my-feature' => false,
        ]));

        $this->assertFalse($gateway->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsNullIfFeaturesAreNotDefined()
    {
        $gateway = new InMemoryGateway($this->getLoader([
        ]));

        $this->assertNull($gateway->accessible('my-feature'));
    }


    protected function getLoader($contents): InMemoryLoader
    {
        return new class($contents) implements InMemoryLoader {

            public function __construct(protected array $contents)
            {}

            public function load(): array
            {
                return $this->contents;
            }
        };
    }
}
