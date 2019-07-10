<?php

namespace YlsIdeas\FeatureFlags\Tests\Repositories;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Repositories\InMemoryRepository;

class InMemoryRepositoryTest extends TestCase
{
    /** @test */
    public function itReturnsTrueIfFeaturesAreAccessible()
    {
        $repository = new InMemoryRepository([
            'my-feature' => true,
        ]);

        $this->assertTrue($repository->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsFalseIfFeaturesAreNotAccessible()
    {
        $repository = new InMemoryRepository([
            'my-feature' => false,
        ]);

        $this->assertFalse($repository->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsNullIfFeaturesAreNotDefined()
    {
        $repository = new InMemoryRepository([
        ]);

        $this->assertNull($repository->accessible('my-feature'));
    }

    /** @test */
    public function itCanFetchAllTheFeaturesAndTheirCurrentState()
    {
        $repository = new InMemoryRepository([
            'my-feature' => true,
        ]);

        $this->assertSame(
            ['my-feature' => true],
            $repository->all()
        );
    }

    /** @test */
    public function itCanStoreTheStateOfFeaturesSwitchedOn()
    {
        $repository = new InMemoryRepository([
        ]);

        $repository->turnOn('my-feature');

        $this->assertTrue($repository->accessible('my-feature'));
    }

    /** @test */
    public function itCanStoreTheStateOfFeaturesSwitchedOff()
    {
        $repository = new InMemoryRepository([
        ]);

        $repository->turnOff('my-feature');

        $this->assertFalse($repository->accessible('my-feature'));
    }
}
