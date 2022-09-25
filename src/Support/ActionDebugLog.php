<?php

namespace YlsIdeas\FeatureFlags\Support;

/**
 * @see \YlsIdeas\FeatureFlags\Tests\Support\ActionDebugLogTest
 */
class ActionDebugLog
{
    public const REASON_RESULT_ALREADY_FOUND = 'result_already_found';
    public const REASON_FILTER = 'filter';
    public const REASON_CACHE = 'cache';
    public const REASON_RESULT = 'result_found';
    public const REASON_NO_RESULT = 'no_result';

    public array $decisions = [];

    public function __construct(public ?string $file, public ?int $line)
    {
    }

    public function addDecision(string $pipe, string $reason, ?bool $result = null): void
    {
        $this->decisions[] = ['pipe' => $pipe, 'reason' => $reason, 'result' => $result];
    }
}
