## v0.4.4

*  Fix "Content-Type" header #4

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
