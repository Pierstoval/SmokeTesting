<?php

namespace App\Tests;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTest extends WebTestCase
{
    use FunctionalSmokeTester;

    #[TestWith(['GET', '/other_param', 'get_param_with_default'])]
    #[TestWith(['GET', '/200', 'get_200'])]
    #[TestWith(['GET', '/302', 'get_302'])]
    #[TestWith(['GET', '/400', 'get_400'])]
    #[TestWith(['GET', '/500', 'get_500'])]
    #[TestWith(['GET', '/payload', 'get_with_payload'])]
    #[TestWith(['GET', '/json/valid', 'json_valid'])]
    #[TestWith(['GET', '/json/valid-header', 'json_valid_header'])]
    #[TestWith(['GET', '/json/missing_header', 'json_missing_header'])]
    #[TestWith(['GET', '/json/invalid', 'json_invalid'])]
    #[TestWith(['GET', '/cookie/value', 'cookie_value'])]
    #[TestWith(['GET', 'http://test.localhost/host/fixed', 'host_fixed'])]
    #[TestWith(['GET', 'http://localhost/scheme/fixed', 'scheme_fixed'])]
    #[TestWith(['GET', '/content-type', 'content_type'])]
    #[TestWith(['POST', '/post', 'post_route'])]
    #[TestDox('$method $url ($route)')]
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
