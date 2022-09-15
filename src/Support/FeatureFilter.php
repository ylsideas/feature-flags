<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Support\Str;

class FeatureFilter
{
    /**
     * @param string[] $rules
     */
    public function __construct(protected array $rules)
    {
    }

    public function fails(string $feature): bool
    {
        return ! $this->passes($feature);
    }

    public function passes(string $feature): bool
    {
        foreach ($this->rules as $rule) {
            if ($this->checkPattern($rule, $feature)) {
                return true;
            }
        }

        return false;
    }

    protected function checkPattern(string $rule, string $feature): string
    {
        $negative = Str::startsWith($rule, '!');

        if ($negative) {
            $rule = Str::after($rule, '!');
        }

        $rule = Str::beforeLast($rule, '*');

        return $negative xor Str::startsWith($feature, $rule);
    }


}
