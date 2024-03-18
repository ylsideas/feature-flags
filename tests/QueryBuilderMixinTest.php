<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class QueryBuilderMixinTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FeatureFlagsServiceProvider::class,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('positiveSqlStatements')]
    public function test_modifying_queries_when_the_feature_is_enabled(bool $flag, string $expectedSql)
    {
        // Laravel 11 for some reason changed how SQL is generated
        if (!InstalledVersions::satisfies(new VersionParser(), 'illuminate/contracts', '^11.0')) {
            $expectedSql = Str::replace('"', '`', $expectedSql);
        }
        Features::fake(['my-feature' => $flag]);

        $sql = DB::table('users')
            ->whenFeatureIsAccessible('my-feature', fn (Builder $query) => $query->where('id', 1))
            ->toSql();

        $this->assertSame($expectedSql, $sql);
    }

    public static function positiveSqlStatements(): \Generator
    {
        yield 'flag is true' => [
            true,
            'select * from "users" where "id" = ?',
        ];
        yield 'flag is false' => [
            false,
            'select * from "users"',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('negativeSqlStatements')]
    public function test_modifying_queries_when_the_feature_is_not_enabled(bool $flag, string $expectedSql)
    {
        // Laravel 11 for some reason changed how SQL is generated
        if (!InstalledVersions::satisfies(new VersionParser(), 'illuminate/contracts', '^11.0')) {
            $expectedSql = Str::replace('"', '`', $expectedSql);
        }
        Features::fake(['my-feature' => $flag]);

        $sql = DB::table('users')
            ->whenFeatureIsNotAccessible('my-feature', fn (Builder $query) => $query->where('id', 1))
            ->toSql();

        $this->assertSame($expectedSql, $sql);
    }

    public static function negativeSqlStatements(): \Generator
    {
        yield 'flag is true' => [
            true,
            'select * from "users"',
        ];
        yield 'flag is false' => [
            false,
            'select * from "users" where "id" = ?',
        ];
    }
}
