<?php

namespace YlsIdeas\FeatureFlags\Tests\Support;

use PHPUnit\Framework\TestCase;
use YlsIdeas\FeatureFlags\Support\ActionDebugLog;

class ActionDebugLogTest extends TestCase
{
    public function test_it_stores_the_file_and_line_location(): void
    {
        $log = new ActionDebugLog('test.php', 10);

        $this->assertSame(10, $log->line);
        $this->assertSame('test.php', $log->file);
    }

    public function test_it_stores_the_log_entries(): void
    {
        $log = new ActionDebugLog('test.php', 10);

        $log->addDecision('test', ActionDebugLog::REASON_NO_RESULT, null);
        $log->addDecision('test2', ActionDebugLog::REASON_FILTER);
        $log->addDecision('test3', ActionDebugLog::REASON_RESULT, true);

        $this->assertSame([
            ['pipe' => 'test', 'reason' => ActionDebugLog::REASON_NO_RESULT, 'result' => null],
            ['pipe' => 'test2', 'reason' => ActionDebugLog::REASON_FILTER, 'result' => null],
            ['pipe' => 'test3', 'reason' => ActionDebugLog::REASON_RESULT, 'result' => true],
        ], $log->decisions);
    }
}
