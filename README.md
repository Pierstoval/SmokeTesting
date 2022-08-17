# SmokeTesting with Symfony

A very small lib that you can use to create tons of smoke tests for your Symfony application.

Particularly useful for big untested Symfony legacy projects.

## Installation

```
composer require pierstoval/smoke-testing
```

## Usage

* Configure PHPUnit for your application.
* Create a test case extending Symfony's `WebTestCase` class.
* Add the `SmokeTestAllRoutes` trait to your class.
* Run PHPUnit.

### Test case example

```php
<?php

namespace App\Tests;

use Pierstoval\SmokeTesting\SmokeTestAllRoutes;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AllRoutesTest extends WebTestCase
{
    use SmokeTestAllRoutes; // This does all the trick!
}
```

## What does it do?

The `SmokeTestAllRoutes` trait will find all HTTP routes of your application by using Symfony's Router, and will run a simple HTTP request on each by using Symfony's HTTP Client.<br>
If the request returns an HTTP 500, the test will fail.<br>
Otherwise, even with HTTP 4**, the test will suceed.
