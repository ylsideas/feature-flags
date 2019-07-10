<?php

namespace YlsIdeas\FeatureFlags\Controllers;

use YlsIdeas\FeatureFlags\Facades\Features;

class FeaturesController
{
    public function __invoke()
    {
        return response()
            ->json(['features' => Features::all()]);
    }
}
