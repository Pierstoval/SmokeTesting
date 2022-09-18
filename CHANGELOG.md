## v0.3.0

* Rename `SmokeTestAllRoutes` to `SmokeTestStaticRoutes` (issue #3 by @stof).
* Make `SmokeTestStaticRoutes` trait run a smoke test on all static routes **plus** the routes for which the defaults are enough to generate an url.
* Use `$router->generateUrl()` instead of `$route->getPath()` to get the URL of the route to test.
* Add more tests (more coming, aiming maximum coverage of all features).
* Completely refactor project's testing setup. Previous one was based on Symfony Process and was extra slow, new one is based on just instantiating tests and running them directly, and it's blazing fast.
* Fixed an issue where it wasn't impossible to use `FunctionalSmokeTester::runFunctionalTest()` multiple times.
* Updated dependencies in `composer.json` to better fit with what's really needed by the package.

## v0.2.0

* Added support for expectation callables
* Added support for JSON response expectation
* Added support for JSON parts expectation, allowing you to assert that *some* parts (of any nesting level) of the response JSON to match your expectations

## v0.1.0

Initial version
