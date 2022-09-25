<?php

namespace YlsIdeas\FeatureFlags\Support;

use Illuminate\Database\Query\Builder;
use YlsIdeas\FeatureFlags\Facades\Features;

/**
 * @mixin Builder
 */
class QueryBuilderMixin
{
    public function whenFeatureIsAccessible(): callable
    {
        return fn (string $feature, callable $action): \Illuminate\Contracts\Database\Query\Builder => $this->when(Features::accessible($feature), $action);
    }

    public function whenFeatureIsNotAccessible(): callable
    {
        return fn (string $feature, callable $action): \Illuminate\Contracts\Database\Query\Builder => $this->when(! Features::accessible($feature), $action);
    }
}
