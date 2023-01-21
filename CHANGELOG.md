# Changelog

All notable changes to `feature-flags` will be documented in this file

## 2.3.1 - 2023-01-21

### What's Changed

- Automates updating Facade DocBlock by @peterfox in https://github.com/ylsideas/feature-flags/pull/52
- Fix facade docs to contain fake methods by @peterfox in https://github.com/ylsideas/feature-flags/pull/53

**Full Changelog**: https://github.com/ylsideas/feature-flags/compare/v2.3.0...v2.3.1

## 2.3.0 - 2023-01-15

### What's Changed

- Implement a middleware message by @peterfox in https://github.com/ylsideas/feature-flags/pull/49
- Default config changes by @peterfox in https://github.com/ylsideas/feature-flags/pull/50
- Support Laravel 10 by @peterfox in https://github.com/ylsideas/feature-flags/pull/51

**Full Changelog**: https://github.com/ylsideas/feature-flags/compare/v2.2.0...v2.3.0

## 2.1.0 - 2022-10-22

- Adds a new system for debugging features that are accessed.
- Testing a feature now is easy to do via the Features facade.
- You can now add a handler for when features should be expired.
- Adds a mixin for the Eloquent Query Builder allowing you to use the methods whenFeatureIsAccessible() and whenFeatureIsNotAccessible().

## 2.0.0 - 2022-09-18

- Breaking Changes. Adds a pipeline and gateway system over the old repository system. Allows for multiple use
- of the same driver within the pipeline. Changes config to in_memory driver. Adds a gate based driver.

## 1.2.1 - 2019-12-08

- Fixes a mistake with the `update_on_resolve` config option not being used for the Chain repository.

## 1.2.0 - 2019-10-25

- Adds a new console command `feature:state` to report the current state of a feature flag.

## 1.1.0 - 2019-09-06

- Fixes incorrect logic for handling features that are off and being check via the middleware or validations.

## 1.0.1 - 2019-09-06

- Tested to work with Laravel 6.0 release

## 1.0.0 - 2019-07-10

- initial release
