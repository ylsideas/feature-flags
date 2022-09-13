<?php

namespace YlsIdeas\FeatureFlags\Commands;

use Illuminate\Console\Command;
use YlsIdeas\FeatureFlags\Facades\Features;

class SwitchOnFeature extends Command
{
    protected $signature = 'feature:on {gateway} {feature}';

    protected $description = 'Switches a specified feature flag on';

    public function handle(): int
    {
        Features::turnOn($this->argument('gateway'), $this->argument('feature'));

        $this->line(
            sprintf(
                'Feature `%s` has been turned on',
                $this->argument('feature')
            )
        );

        return self::SUCCESS;
    }
}
