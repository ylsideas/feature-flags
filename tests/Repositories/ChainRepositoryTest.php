<?php

namespace YlsIdeas\FeatureFlags\Tests\Repositories;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Manager;
use YlsIdeas\FeatureFlags\Repositories\ChainRepository;
use YlsIdeas\FeatureFlags\Repositories\DatabaseRepository;
use YlsIdeas\FeatureFlags\Repositories\InMemoryRepository;

class ChainRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @test */
    public function itCanBeInitialised()
    {
        $manager = \Mockery::mock(Manager::class);

        $repository = new ChainRepository(
            $manager,
            ['database', 'config'],
            'database'
        );

        $this->assertInstanceOf(ChainRepository::class, $repository);
    }

    /** @test */
    public function itWarnsAgainstProvidingNoRepositories()
    {
        $manager = \Mockery::mock(Manager::class);
        $this->expectExceptionMessage('One repository must be supplied for $repositories');

        $repository = new ChainRepository(
            $manager,
            [],
            'database'
        );

        $this->assertInstanceOf(ChainRepository::class, $repository);
    }

    /** @test */
    public function itReturnsTrueIfFeaturesAreAccessible()
    {
        $manager = \Mockery::mock(Manager::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);

        $manager->shouldReceive('driver')
            ->with('database')
            ->once()
            ->andReturn($databaseRepository);

        $databaseRepository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(true);

        $repository = new ChainRepository(
            $manager,
            ['database', 'config'],
            'database'
        );

        $this->assertTrue($repository->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsFalseIfFeaturesAreNotAccessible()
    {
        $manager = \Mockery::mock(Manager::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);

        $manager->shouldReceive('driver')
            ->with('database')
            ->once()
            ->andReturn($databaseRepository);

        $databaseRepository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(false);

        $repository = new ChainRepository(
            $manager,
            ['database', 'config'],
            'database'
        );

        $this->assertFalse($repository->accessible('my-feature'));
    }

    /** @test */
    public function itReturnsNullIfFeaturesAreNotDefined()
    {
        $manager = \Mockery::mock(Manager::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);
        $inMemoryRepository = \Mockery::mock(InMemoryRepository::class);

        $manager->shouldReceive('driver')
            ->with('database')
            ->once()
            ->andReturn($databaseRepository);

        $manager->shouldReceive('driver')
            ->with('config')
            ->once()
            ->andReturn($inMemoryRepository);

        $databaseRepository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(null);

        $inMemoryRepository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(null);

        $repository = new ChainRepository(
            $manager,
            ['database', 'config'],
            'database'
        );

        $this->assertNull($repository->accessible('my-feature'));
    }

    /** @test */
    public function itUpdatesThePreviousStateOfThe()
    {
        $manager = \Mockery::mock(Manager::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);
        $inMemoryRepository = \Mockery::mock(InMemoryRepository::class);

        $manager->shouldReceive('driver')
            ->with('database')
            ->twice()
            ->andReturn($databaseRepository);

        $manager->shouldReceive('driver')
            ->with('config')
            ->once()
            ->andReturn($inMemoryRepository);

        $databaseRepository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(null);

        $inMemoryRepository->shouldReceive('accessible')
            ->with('my-feature')
            ->once()
            ->andReturn(true);

        $databaseRepository->shouldReceive('turnOn')
            ->with('my-feature')
            ->once();

        $repository = new ChainRepository(
            $manager,
            ['database', 'config'],
            'database',
            true
        );

        $this->assertTrue($repository->accessible('my-feature'));
    }

    /** @test */
    public function itCanFetchAllTheFeaturesAndTheirCurrentState()
    {
        $manager = \Mockery::mock(Manager::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);
        $inMemoryRepository = \Mockery::mock(InMemoryRepository::class);

        $manager->shouldReceive('driver')
            ->with('database')
            ->once()
            ->andReturn($databaseRepository);

        $manager->shouldReceive('driver')
            ->with('config')
            ->once()
            ->andReturn($inMemoryRepository);

        $databaseRepository->shouldReceive('all')
            ->once()
            ->andReturn([
                'my-feature' => true,
                'my-second-feature' => true,
                'my-fourth-feature' => false,
            ]);

        $inMemoryRepository->shouldReceive('all')
            ->once()
            ->andReturn([
                'my-feature' => false,
                'my-third-feature' => true,
                'my-fourth-feature' => true,
            ]);

        $repository = new ChainRepository(
            $manager,
            ['config', 'database'],
            'database'
        );

        $this->assertSame($repository->all(), [
            'my-feature' => false,
            'my-second-feature' => true,
            'my-fourth-feature' => true,
            'my-third-feature' => true,
        ]);
    }

    public function itRespectsTheChainOrder()
    {
        $manager = \Mockery::mock(Manager::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);
        $inMemoryRepository = \Mockery::mock(InMemoryRepository::class);

        $manager->shouldReceive('driver')
            ->with('database')
            ->once()
            ->andReturn($databaseRepository);

        $manager->shouldReceive('driver')
            ->with('config')
            ->once()
            ->andReturn($inMemoryRepository);

        $inMemoryRepository->shouldReceive('all')
            ->once()
            ->andReturn([
                'my-feature' => false,
            ]);

        $databaseRepository->shouldReceive('all')
            ->once()
            ->andReturn([
                'my-feature' => true,
            ]);

        $repository = new ChainRepository(
            $manager,
            ['config', 'database'],
            'database'
        );

        $this->assertSame($repository->all(), [
            'my-feature' => false,
        ]);
    }

    /** @test */
    public function itCanStoreTheStateOfFeaturesSwitchedOn()
    {
        $manager = \Mockery::mock(Manager::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);
        $inMemoryRepository = \Mockery::mock(InMemoryRepository::class);

        $manager->shouldReceive('driver')
            ->with('database')
            ->once()
            ->andReturn($databaseRepository);

        $manager->shouldReceive('driver')
            ->with('config')
            ->once()
            ->andReturn($inMemoryRepository);

        $databaseRepository->shouldReceive('turnOn')
            ->with('my-feature')
            ->once();

        $inMemoryRepository->shouldReceive('turnOn')
            ->with('my-feature')
            ->once();

        $repository = new ChainRepository(
            $manager,
            ['database', 'config'],
            'database'
        );

        $repository->turnOn('my-feature');
    }

    /** @test */
    public function itCanStoreTheStateOfFeaturesSwitchedOff()
    {
        $manager = \Mockery::mock(Manager::class);
        $databaseRepository = \Mockery::mock(DatabaseRepository::class);
        $inMemoryRepository = \Mockery::mock(InMemoryRepository::class);

        $manager->shouldReceive('driver')
            ->with('database')
            ->once()
            ->andReturn($databaseRepository);

        $manager->shouldReceive('driver')
            ->with('config')
            ->once()
            ->andReturn($inMemoryRepository);

        $databaseRepository->shouldReceive('turnOff')
            ->with('my-feature')
            ->once();

        $inMemoryRepository->shouldReceive('turnOff')
            ->with('my-feature')
            ->once();

        $repository = new ChainRepository(
            $manager,
            ['database', 'config'],
            'database'
        );

        $repository->turnOff('my-feature');
    }
}
