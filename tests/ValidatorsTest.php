<?php

namespace YlsIdeas\FeatureFlags\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use YlsIdeas\FeatureFlags\Facades\Features;
use YlsIdeas\FeatureFlags\FeatureFlagsServiceProvider;

class ValidatorsTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FeatureFlagsServiceProvider::class,
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        Config::set('features.default', 'config');
    }

    /** @test */
    public function validationRuleWithFeatureOnAndAttributeIncluded()
    {
        Features::turnOn('my-feature');

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
        Features::turnOn('my-feature');

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
        Features::turnOff('my-feature');

        $validator = Validator::make([
        ], [
            'exists' => 'requiredWithFeature:my-feature',
        ]);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function validationRuleWithFeatureOnAndAttributeMissingWhileExpectingOn()
    {
        Features::turnOff('my-feature');

        $validator = Validator::make([
        ], [
            'exists' => 'requiredWithFeature:my-feature,on',
        ]);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function validationRuleWithFeatureOffAndAttributeIncludedWhileExpectingOff()
    {
        Features::turnOff('my-feature');

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
        Features::turnOn('my-feature');

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
