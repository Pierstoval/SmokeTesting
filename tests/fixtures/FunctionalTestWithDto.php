<?php

namespace App\Tests;

use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTest extends WebTestCase
{
    use FunctionalSmokeTester;

    public function testRouteGetParamWithDefaultWithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/other_param')
                ->withMethod('GET')
                ->expectRouteName('get_param_with_default')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/other_param'))
        );
    }

    public function testRouteGet200WithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/200')
                ->withMethod('GET')
                ->expectRouteName('get_200')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/200'))
        );
    }

    public function testRouteGet302WithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/302')
                ->withMethod('GET')
                ->expectRouteName('get_302')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/302'))
        );
    }

    public function testRouteGet400WithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/400')
                ->withMethod('GET')
                ->expectRouteName('get_400')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/400'))
        );
    }

    public function testRouteGet500WithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/500')
                ->withMethod('GET')
                ->expectRouteName('get_500')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/500'))
        );
    }

    public function testRouteGetWithPayloadWithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/payload')
                ->withMethod('GET')
                ->expectRouteName('get_with_payload')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/payload'))
        );
    }

    public function testRouteJsonValidWithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/valid')
                ->withMethod('GET')
                ->expectRouteName('json_valid')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/json/valid'))
        );
    }

    public function testRouteJsonValidHeaderWithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/valid-header')
                ->withMethod('GET')
                ->expectRouteName('json_valid_header')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/json/valid-header'))
        );
    }

    public function testRouteJsonMissingHeaderWithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/missing_header')
                ->withMethod('GET')
                ->expectRouteName('json_missing_header')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/json/missing_header'))
        );
    }

    public function testRouteJsonInvalidWithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/invalid')
                ->withMethod('GET')
                ->expectRouteName('json_invalid')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/json/invalid'))
        );
    }

    public function testRouteCookieValueWithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/cookie/value')
                ->withMethod('GET')
                ->expectRouteName('cookie_value')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/cookie/value'))
        );
    }

    public function testRouteHostFixedWithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('http://test.localhost/host/fixed')
                ->withMethod('GET')
                ->expectRouteName('host_fixed')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', 'http://test.localhost/host/fixed'))
        );
    }

    public function testRouteSchemeFixedWithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('http://localhost/scheme/fixed')
                ->withMethod('GET')
                ->expectRouteName('scheme_fixed')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', 'http://localhost/scheme/fixed'))
        );
    }

    public function testRouteContentTypeWithMethodGet(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/content-type')
                ->withMethod('GET')
                ->expectRouteName('content_type')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('GET', '/content-type'))
        );
    }

    public function testRoutePostRouteWithMethodPost(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/post')
                ->withMethod('POST')
                ->expectRouteName('post_route')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('POST', '/post'))
        );
    }

    public function assertStatusCodeLessThan500(string $method, string $url): \Closure
    {
        return function (KernelBrowser $browser) use ($method, $url) {
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
