## v1.2.2

* Fix issues with phpunit test cases
* (CI) Remove github action deprecation

## v1.2.1

* Ensure compatibility with PHPUnit 12.

## v1.2.0

* Make sure the library is usable for PHP 8.3 and 8.4
* Add `--use-attributes` option to the `make:smoke-tests` command: this will generate smoke tests using PHPUnit's `#[TestWith]` attribute 
* Add `--use-provider` option to the `make:smoke-tests` command: this will generate smoke tests using PHPUnit's `#[DataProvider]` attribute<br>(note: both aforementioned attributes cannot be used at the same time)

## v1.1.1

* Fixes how routes with dynamic host and schemes were taken in account while they shouldn't have.
* Fix and enhance how Routes extractor references URLs of routes. If your routes have a default `host` or one or multiple `schemes`, the extractor will change the url to be an absolute URL instead of just the path, to ease debugging.

## v1.1.0

* **BC break**: Disable possibility to use an `E_USER_*` constant in `SMOKE_TESTING_ROUTES_METHODS`.
* Fix `SMOKE_TESTING_ROUTES_METHODS`'s inconsistent configuration.

## v1.0.2

* Fix Routes extractor not providing proper naming for the test argument

## v1.0.1

* Add the `SMOKE_TESTING_ROUTES_METHODS` environment variable to disable the deprecation message

## v1.0.0

* Project seems quite stable, let's roll for v1.0.0
* **BC breaks**: drop some version compatibilities for maintenance reasons:
  * Dropped support for PHP < 8.0
  * Dropped support for Symfony < 6.1
* Made the project compatible with Symfony 7.0 and PHP 8.2
* Refactored CI and fixture app to reflect these new requirements 

## v0.6.3

* Remove a Symfony 6.3 type-based deprecation
* Updated phpunit 10 config

## v0.6.2

* Make the lib compatible with PHPUnit 10 with no regression in the codebase.

## v0.6.1

* Use `.gitattributes` to remove useless files from Composer package (@ker0x #6)

## v0.6.0

* Create a Maker command to generate all routes tests in one single class
* Make it usable with PHP 7.4
* Add Bundle class and update package type to symfony-bundle to automate the bundle setup with Flex
* Document new features in the readme

## v0.5.0

* Make the project compatible from PHP 8.0 up to 8.2, and from Symfony 5.4 up to 6.2

## v0.4.4

* Fix "Content-Type" header #4

## v0.4.3

* Fix JSON header compatibility with charset parameter.

## v0.4.2

* Add ability to execute an action before making the actual HTTP request via `FunctionalTestData->withCallbackBeforeRequest()` method.

## v0.4.1

* Fix a bug calling "expectation callables" twice
* Add support for expected HTTP headers in the Response
* Add support server parameters injection at request-time to the Client. Note: this part is experimental and would need wide testing in different types of Symfony apps before being functionally stable.

## v0.4.0

* Breaking change: Make `SmokeTestStaticRoutes` an abstract class to ease customizing it with hooks.
* Create `SmokeTestStaticRoutes::beforeRequest(...)` to be able to hook before the HTTP request is made to the backend.
* Create `SmokeTestStaticRoutes::afterRequest(...)` to be able to hook jut after the HTTP request is made, and before the assertion is made.
* Create `SmokeTestStaticRoutes::afterAssertion(...)` to be able to add more assertions right after an HTTP request is made to the backend.

## v0.3.0

* Breaking change: Rename `SmokeTestAllRoutes` to `SmokeTestStaticRoutes` (issue #3 by @stof).
* Make `SmokeTestStaticRoutes` trait run a smoke test on all static routes **plus** the routes for which the defaults are enough to generate an url.
* Use `$router->generateUrl()` instead of `$route->getPath()` to get the URL of the route to test.
* Add more tests (more coming, aiming maximum coverage of all features).
* Completely refactor project's testing setup. Previous one was based on Symfony Process and was extra slow, new one is based on just instantiating tests and running them directly, and it's blazing fast.
* Fixed an issue where it wasn't impossible to use `FunctionalSmokeTester::runFunctionalTest()` multiple times.
* Updated dependencies in `composer.json` to better fit with what's really needed by the package.

## v0.2.0

* Add support for expectation callables
* Add support for JSON response expectation
* Add support for JSON parts expectation, allowing you to assert that *some* parts (of any nesting level) of the response JSON to match your expectations

## v0.1.0

Initial version
