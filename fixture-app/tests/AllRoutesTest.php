<?php

namespace App\Tests;

use Pierstoval\SmokeTesting\SmokeTestStaticRoutes;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AllRoutesTest extends WebTestCase
{
    use SmokeTestStaticRoutes;
}
