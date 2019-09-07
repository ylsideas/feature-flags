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
        if (! is_string($parameters[0] ?? null)) {
            throw new \InvalidArgumentException(
                'First parameter for `requiredWithFeature` validation rule must be the name of the feature'
            );
        }

        $featureState =
            $this->check($parameters[1] ?? 'on')
                ? ! Features::accessible($parameters[0])
                : Features::accessible($parameters[0]);

        if (! $featureState) {
            return $this->validateRequired($attribute, $value);
        }

        return true;
    }
}
