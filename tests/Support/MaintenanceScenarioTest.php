<?php

namespace YlsIdeas\FeatureFlags\Tests\Support;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Support\MaintenanceScenario;

class MaintenanceScenarioTest extends TestCase
{
    #[DataProvider('scenarioOptions')]
    public function test_it_builds_a_maintenance_scenario(callable $builder, bool $polarity, array $result): void
    {
        $scenario = new MaintenanceScenario();
        /** @var MaintenanceScenario $scenario */
        $scenario = $builder($scenario);

        $this->assertSame($polarity, $scenario->onEnabled);
        $this->assertSame($result, $scenario->toArray());
    }

    public static function scenarioOptions(): Generator
    {
        yield 'secret' => [
            fn (MaintenanceScenario $scenario): MaintenanceScenario => $scenario
                ->whenEnabled('feature')
                ->secret('my-password'),
            true,
            ['secret' => 'my-password'],
        ];

        yield 'except' => [
            fn (MaintenanceScenario $scenario): MaintenanceScenario => $scenario
                ->whenEnabled('feature')
                ->exceptPaths(['/', '/nova/*']),
            true,
            ['except' => ['/', '/nova/*']],
        ];

        yield 'retry' => [
            fn (MaintenanceScenario $scenario): MaintenanceScenario => $scenario
                ->whenEnabled('feature')
                ->retry(10),
            true,
            ['retry' => 10],
        ];

        yield 'refresh' => [
            fn (MaintenanceScenario $scenario): MaintenanceScenario => $scenario
                ->whenEnabled('feature')
                ->refresh(10),
            true,
            ['refresh' => '10'],
        ];

        yield 'status' => [
            fn (MaintenanceScenario $scenario): MaintenanceScenario => $scenario
                ->whenEnabled('feature')
                ->statusCode(500),
            true,
            ['status' => 500],
        ];

        yield 'redirect' => [
            fn (MaintenanceScenario $scenario): MaintenanceScenario => $scenario
                ->whenEnabled('feature')
                ->redirect('/'),
            true,
            ['redirect' => '/'],
        ];

        yield 'template' => [
            fn (MaintenanceScenario $scenario): MaintenanceScenario => $scenario
                ->whenEnabled('feature')
                ->template('<p>test</p>'),
            true,
            ['template' => '<p>test</p>'],
        ];

        yield 'disabled' => [
            fn (MaintenanceScenario $scenario): MaintenanceScenario => $scenario
                ->whenDisabled('feature'),
            false,
            [],
        ];
    }
}
