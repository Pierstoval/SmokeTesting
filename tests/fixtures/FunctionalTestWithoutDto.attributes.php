<?php

namespace App\Tests;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTest extends WebTestCase
{

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
        $client = static::createClient();
        $client->request('GET', '/');

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
