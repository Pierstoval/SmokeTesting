# Smoke Testing with Symfony

A very small lib meant to ease writing simple tests for your Symfony applications.

Particularly useful for big untested Symfony legacy projects.

This lib was originally inspired by a [tweet from @SmaineDev](https://twitter.com/SmaineDev/status/1559542937696043008), you can find him on [his Github (@ismail1432)](https://github.com/ismail1432) too.

## Why smoke testing

[Smoke testing](https://en.wikipedia.org/wiki/Smoke_testing_(software)) is a way to quickly assert whether your project is viable for *unit* or *functional* testing.

To smoke-test an app, it is common to execute many parts of the app itself and just make sure it doesn't throw errors.

> An analogy could be that it is like starting the engine of your car to make sure it starts and doesn't throw smoke nor explode (hence "smoke" testing 😉), but not individually testing the windshield wipers, brake, or gear box.

In the world of web development, smoke testing is often about opening all pages of a website and making sure they don't return an HTTP server error (status code > 500), and sometimes testing 404 or other HTTP client error codes.

Another **really important** use of smoke testing is when you work on big legacy projects that have no tests and/or no documentation.<br>
Running basic smoke testing (like when using this library) **comes with a very low cost** and can **check the whole project's average health**.<br>
Plus, adding **manual testing** (see the [Smoke-test routes manually](#smoke-test-routes-manually) section) allows you to have **more control** and more **advanced settings** for your tests (such as adding HTTP headers, making expectations on page content, etc.).

## Installation

```
composer require --dev pierstoval/smoke-testing
```

## Usage

First, you need to configure PHPUnit for your application (see the [Testing](https://symfony.com/doc/current/testing.html) section on Symfony docs).

Now choose between [smoke testing all your routes at once](#-smoke-test-all-routes) or [smoke testing routes manually](#-smoke-test-routes-manually) (see below).

### 🌊 Smoke test ALL routes

* Create a test case.
* Make it extend `SmokeTestStaticRoutes`.
* Run PHPUnit.

Example:

```php
<?php

namespace App\Tests;

use Pierstoval\SmokeTesting\SmokeTestStaticRoutes;

class AllRoutesTest extends SmokeTestStaticRoutes
{
    // That's all!
}
```

That's it!

You need to set this up only once in your project.

What does it do?

The `SmokeTestStaticRoutes` class already contains a PHPUnit test that will **find all static routes** of your application by using the Symfony Router, and will **run a single HTTP request on each** by using Symfony's HTTP Client.<br>
If the request returns an HTTP status code **>= 500**, the test will **fail**.<br>
**Otherwise**, even with HTTP 400 or 404, the test will **suceed**.

**Note:** This class **will also look for your non-static routes**! Routes that have `defaults` for all their dynamic parameters will be included in the smoke test suite, maximizing the number of testable routes when possible.

> What is a static route?<br>
> To sum it up quickly, `/api/endpoint` is a static route because it does not have any dynamic parameter.<br>
> On the other hand, `/{_locale}/posts` is not static, because it contains the `{_locale}` parameter that is dynamic.
> 
> If your route contains `defaults` for all its `{...}` dynamic parameters, it means the Symfony Router is capable of generating an URL without having to pass these parameters to it, therefore it is smoke-testable by this package.

As said in the intro, 4** HTTP codes can be perfectly normal and expected, like when you have some sections of your app that depend on authentication.

> On a personal note, I recommend you create tests for at least 400/401/403 codes on your projects.
> Most of them are based on client input that has to be validated, or authentication, which are critical entry points of your application, and therefore must be thoroughly tested.

#### Customize `SmokeTestStaticRoutes` usage

To be able to customize your HTTP testing, for example when you have lots of routes and many of them are under authentication, you can use the available extension points in the `SmokeTestStaticRoutes` class:

* `beforeRequest(KernelBrowser $client, string $routeName, string $routePath)`<br>
  Allows you to hook before the HTTP request is made to the backend. That's the place where you would for instance call your app backend and create an authentication token to add to the HTTP Client object.<br>Since the route name and path are provided, you can even filter based on route name or path (like adding auth only for routes starting with `/api/` if you like).
* `afterRequest(KernelBrowser $client, string $routeName, string $routePath)`<br>
  Allows you to hook jut after the HTTP request is made, and before the assertion over non-500-like HTTP code is made.
* `afterAssertion(KernelBrowser $client, string $routeName, string $routePath)`<br>
  Allows you to add more assertions right after an HTTP request is made to the backend, in case you want to add more assertions.

If you implement these methods (that are defined as empty by default in the class), you must implement them at least as `protected`, and non-static.

### 🔬 Smoke test routes **manually**

Instead of (or conjointedly to) checking all routes of your app, you can run a list of URLs of your choice and have control on all request parameters and test assertions/expectations.

> That is the method I would recommend for a gentle and graceful start with smoke testing,
> because this method gives you a lot of control on your tests, allowing you to create tests that are way more exhaustive than simple smoke tests (making them more like functional tests, in the end).

* Add the `FunctionalSmokeTester` trait to your class.
* Create functional test data using the `FunctionalTestData` class (see example below).
* Execute the `$this->runFunctionalTest` method in your test case with your `FunctionalTestData` instance as first argument.
* Run PHPUnit.

Example:

```php
<?php

namespace App\Tests;

use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AllRoutesTest extends WebTestCase
{
    use FunctionalSmokeTester; // Allows using the "$this->runFunctionalTest()" method.
    
    public function testGet200(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/my-route')
                ->expectRouteName('my_successful_route')
                ->expectStatusCode(200)
                ->expectTextToBePresent('Hello world!')
        );
    }
}
```

For convenience, you can also create a **data provider** to execute only the URLs you need:

```php
<?php

namespace App\Tests;

use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AllRoutesTest extends WebTestCase
{
    use FunctionalSmokeTester;

    /**
     * @dataProvider provideTestUrls
     */
    public function testMyUrls(FunctionalTestData $testData): void
    {
        $this->runFunctionalTest($testData);
    }
    
    public function provideTestUrls(): \Generator
    {
        yield '/my-route' => FunctionalTestData::withUrl('/my-route')
                ->expectRouteName('my_successful_route')
                ->expectStatusCode(200)
                ->expectTextToBePresent('Hello world!');

        yield '/my-other-route' => FunctionalTestData::withUrl('/my-other-route')
                ->withHttpHeader('Authorization', 'Bearer 2d5b0cfb531745668')
                ->expectRouteName('my_other_route')
                ->expectStatusCode(200)
                ->expectTextToBePresent('{"message": "Bonjour!"}');
    }
}
```

The whole API of the `FunctionalTestData` class is explained there:

* [List of available Request data/input](./docs/RequestData.md)
* [List of available expectations](./docs/Expectations.md)
