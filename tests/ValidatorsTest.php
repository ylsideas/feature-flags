<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class ValidatorsTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FeatureFlagsServiceProvider::class,
        ];
    }

    public function test_validation_rule_with_feature_on_and_attribute_included(): void
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(true);

        $validator = Validator::make([
            'exists' => 'yes',
        ], [
            'exists' => 'requiredWithFeature:my-feature',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_validation_rule_with_feature_on_and_attribute_missing(): void
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(true);


        $validator = Validator::make([
        ], [
            'exists' => 'requiredWithFeature:my-feature',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('exists'));
    }

    public function test_validation_rule_with_feature_off_and_attribute_missing(): void
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(false);


        $validator = Validator::make([
        ], [
            'exists' => 'requiredWithFeature:my-feature',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_validation_rule_with_feature_on_and_attribute_missing_while_expecting_on(): void
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(false);


        $validator = Validator::make([
        ], [
            'exists' => 'requiredWithFeature:my-feature,on',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_validation_rule_with_feature_off_and_attribute_included_while_expecting_off(): void
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(false);


        $validator = Validator::make([
            'exists' => 'yes',
        ], [
            'exists' => 'requiredWithFeature:my-feature,off',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_validation_rule_with_feature_on_and_attribute_missing_while_expecting_off(): void
    {
        Features::shouldReceive('accessible')
            ->with('my-feature')
            ->andReturn(true);


        $validator = Validator::make([
        ], [
            'exists' => 'requiredWithFeature:my-feature,off',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_rule_must_be_used_with_feature_name_parameter(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $validator = Validator::make([
        ], [
            'exists' => 'requiredWithFeature',
        ]);

        $validator->fails();
    }
}
