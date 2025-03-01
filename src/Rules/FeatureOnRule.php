<?php

namespace YlsIdeas\FeatureFlags\Rules;

use Illuminate\Validation\Concerns\ValidatesAttributes;
use InvalidArgumentException;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\Support\StateChecking;

class FeatureOnRule
{
    use ValidatesAttributes;
    use StateChecking;

    public function validate($attribute, $value, $parameters): bool
    {
        if (! is_string($parameters[0] ?? null)) {
            throw new InvalidArgumentException(
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
