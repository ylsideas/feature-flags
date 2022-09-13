<?php

namespace YlsIdeas\FeatureFlags\Commands;

use Illuminate\Console\Command;
use YlsIdeas\FeatureFlags\Facades\Features;

class SwitchOffFeature extends Command
{
    protected $signature = 'feature:off {gateway} {feature}';

    protected $description = 'Switches a specified feature flag off';

    public function handle(): int
    {
        Features::turnOff($this->argument('gateway'), $this->argument('feature'));

        $this->line(
            sprintf(
                'Feature `%s` has been turned off',
                $this->argument('feature')
            )
        );

        return self::SUCCESS;
    }
}
