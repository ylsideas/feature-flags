<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Illuminate\Support\Facades\Config;
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

    /** @test */
    public function validationRuleWithFeatureOnAndAttributeIncluded()
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

    /** @test */
    public function validationRuleWithFeatureOnAndAttributeMissing()
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

    /** @test */
    public function validationRuleWithFeatureOffAndAttributeMissing()
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

    /** @test */
    public function validationRuleWithFeatureOnAndAttributeMissingWhileExpectingOn()
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

    /** @test */
    public function validationRuleWithFeatureOffAndAttributeIncludedWhileExpectingOff()
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

    /** @test */
    public function validationRuleWithFeatureOnAndAttributeMissingWhileExpectingOff()
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

    /** @test */
    public function ruleMustBeUsedWithFeatureNameParameter()
    {
        $this->expectException(\InvalidArgumentException::class);

        $validator = Validator::make([
        ], [
            'exists' => 'requiredWithFeature',
        ]);

        $validator->fails();
    }
}
