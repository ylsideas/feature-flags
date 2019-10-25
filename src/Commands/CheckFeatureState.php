<?php

namespace YlsIdeas\FeatureFlags\Commands;

use Illuminate\Console\Command;
use YlsIdeas\FeatureFlags\Facades\Features;

class CheckFeatureState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feature:state {feature}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets the current state of the specified feature flag';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \ErrorException
     */
    public function handle()
    {
        $feature = $this->argument('feature');

        if (is_null($feature) || is_array($feature)) {
            throw new \ErrorException('Feature argument must be a string');
        }

        $state = Features::accessible($feature);

        $this->line(
            sprintf(
                'Feature `%s` is currently %s',
                $feature, $state ? 'on' : 'off'
            )
        );
    }
}
