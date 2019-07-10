<?php

namespace YlsIdeas\FeatureFlags\Rules;

use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\Support\StateChecking;
use Illuminate\Validation\Concerns\ValidatesAttributes;

class FeatureOnRule
{
    use ValidatesAttributes, StateChecking;

    public function validate($attribute, $value, $parameters)
    {
        $correctFeatureState = $this->check($parameters[0] ?? 'on')
            ? Features::accessible($parameters[0])
            : ! Features::accessible($parameters[0]);

        return $correctFeatureState
            ? $this->validateRequired($attribute, $value)
            : true;
    }
}
