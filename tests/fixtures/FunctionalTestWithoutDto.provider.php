<?php

namespace App\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTest extends WebTestCase
{

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
        $client = static::createClient();
        $client->request($method, $url);

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            \sprintf(
                'Request "%s %s" for route "%s" returned an internal error.',
                $method, $url, $route
            ),
        );
    }

}
