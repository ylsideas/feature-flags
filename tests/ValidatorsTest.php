<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Support\Facades\Validator;
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

    public function testValidationRuleWithFeatureOnAndAttributeIncluded()
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

    public function testValidationRuleWithFeatureOnAndAttributeMissing()
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

    public function testValidationRuleWithFeatureOffAndAttributeMissing()
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

    public function testValidationRuleWithFeatureOnAndAttributeMissingWhileExpectingOn()
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

    public function testValidationRuleWithFeatureOffAndAttributeIncludedWhileExpectingOff()
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

    public function testValidationRuleWithFeatureOnAndAttributeMissingWhileExpectingOff()
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

    public function testRuleMustBeUsedWithFeatureNameParameter()
    {
        $this->expectException(\InvalidArgumentException::class);

        $validator = Validator::make([
        ], [
            'exists' => 'requiredWithFeature',
        ]);

        $validator->fails();
    }
}
