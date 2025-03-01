# Changelog

All notable changes to `feature-flags` will be documented in this file

## v3.0.0 - 2025-03-01

### What's Changed

* Update workflow for 8.4 by @peterfox in https://github.com/ylsideas/feature-flags/pull/68
* change instance of manager by @uesley in https://github.com/ylsideas/feature-flags/pull/71
* Upgrade static analysis tools & Laravel 12 compatibility by @peterfox in https://github.com/ylsideas/feature-flags/pull/72

### New Contributors

* @uesley made their first contribution in https://github.com/ylsideas/feature-flags/pull/71

**Full Changelog**: https://github.com/ylsideas/feature-flags/compare/v2.6.0...v3.0.0

## v2.6.0 - 2024-05-29

### What's Changed

* Allows for multiple fakes to occur by @peterfox @brunodevel in https://github.com/ylsideas/feature-flags/pull/67

**Full Changelog**: https://github.com/ylsideas/feature-flags/compare/v2.5.0...v2.6.0

## v2.5.0 - 2024-03-19

### What's Changed

* Laravel 11 by @peterfox in https://github.com/ylsideas/feature-flags/pull/65

**Full Changelog**: https://github.com/ylsideas/feature-flags/compare/v2.4.2...v2.5.0

## v2.4.2 - 2023-12-02

### What's Changed

* Fix maintenance mode by @peterfox in https://github.com/ylsideas/feature-flags/pull/62
* Fix a typo in the about command by @peterfox in https://github.com/ylsideas/feature-flags/pull/63

**Full Changelog**: https://github.com/ylsideas/feature-flags/compare/v2.4.1...v2.4.2

## v2.4.1 - 2023-02-09

### What's Changed

- Fixes a typo in the error message for missing in memory config files #55

**Full Changelog**: https://github.com/ylsideas/feature-flags/compare/v2.4.0...v2.4.1

## 2.4.0 - 2023-02-02

### What's Changed

- Maintenance mode with flags by @peterfox in https://github.com/ylsideas/feature-flags/pull/54

**Full Changelog**: https://github.com/ylsideas/feature-flags/compare/v2.3.1...v2.4.0

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
