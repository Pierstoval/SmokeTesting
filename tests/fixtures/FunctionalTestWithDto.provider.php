<?php

namespace App\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTest extends WebTestCase
{
    use FunctionalSmokeTester;

    public static function provideRoutes(): \Generator
    {
        yield 'GET /other_param' => ['GET', '/other_param', 'get_param_with_default'];
        yield 'GET /200' => ['GET', '/200', 'get_200'];
        yield 'GET /302' => ['GET', '/302', 'get_302'];
        yield 'GET /400' => ['GET', '/400', 'get_400'];
        yield 'GET /500' => ['GET', '/500', 'get_500'];
        yield 'GET /payload' => ['GET', '/payload', 'get_with_payload'];
        yield 'GET /json/valid' => ['GET', '/json/valid', 'json_valid'];
        yield 'GET /json/valid-header' => ['GET', '/json/valid-header', 'json_valid_header'];
        yield 'GET /json/missing_header' => ['GET', '/json/missing_header', 'json_missing_header'];
        yield 'GET /json/invalid' => ['GET', '/json/invalid', 'json_invalid'];
        yield 'GET /cookie/value' => ['GET', '/cookie/value', 'cookie_value'];
        yield 'GET http://test.localhost/host/fixed' => ['GET', 'http://test.localhost/host/fixed', 'host_fixed'];
        yield 'GET http://localhost/scheme/fixed' => ['GET', 'http://localhost/scheme/fixed', 'scheme_fixed'];
        yield 'GET /content-type' => ['GET', '/content-type', 'content_type'];
        yield 'POST /post' => ['POST', '/post', 'post_route'];

    }

    #[DataProvider('provideRoutes')]
    public function testRoute(string $method, string $url, string $route): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl($url)
                ->withMethod($method)
                ->expectRouteName($route)
                ->appendCallableExpectation($this->assertStatusCodeLessThan500($method, $url))
        );
    }

    public function assertStatusCodeLessThan500(string $method, string $url): \Closure
    {
        return static function (KernelBrowser $browser) use ($method, $url) {
            $statusCode = $browser->getResponse()->getStatusCode();
            $routeName = $browser->getRequest()->attributes->get('_route', 'unknown');

            static::assertLessThan(
                500,
                $statusCode,
                sprintf('Request "%s %s" for %s route returned an internal error.', $method, $url, $routeName),
            );
        };
    }
}
