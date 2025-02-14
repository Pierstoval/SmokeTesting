<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTest extends WebTestCase
{

    public function testRouteGetParamWithDefaultWithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /other_param" for route "get_param_with_default" returned an internal error.',
        );
    }

    public function testRouteGet200WithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /200" for route "get_200" returned an internal error.',
        );
    }

    public function testRouteGet302WithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /302" for route "get_302" returned an internal error.',
        );
    }

    public function testRouteGet400WithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /400" for route "get_400" returned an internal error.',
        );
    }

    public function testRouteGet500WithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /500" for route "get_500" returned an internal error.',
        );
    }

    public function testRouteGetWithPayloadWithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /payload" for route "get_with_payload" returned an internal error.',
        );
    }

    public function testRouteJsonValidWithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /json/valid" for route "json_valid" returned an internal error.',
        );
    }

    public function testRouteJsonValidHeaderWithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /json/valid-header" for route "json_valid_header" returned an internal error.',
        );
    }

    public function testRouteJsonMissingHeaderWithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /json/missing_header" for route "json_missing_header" returned an internal error.',
        );
    }

    public function testRouteJsonInvalidWithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /json/invalid" for route "json_invalid" returned an internal error.',
        );
    }

    public function testRouteCookieValueWithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /cookie/value" for route "cookie_value" returned an internal error.',
        );
    }

    public function testRouteHostFixedWithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET http://test.localhost/host/fixed" for route "host_fixed" returned an internal error.',
        );
    }

    public function testRouteSchemeFixedWithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET http://localhost/scheme/fixed" for route "scheme_fixed" returned an internal error.',
        );
    }

    public function testRouteContentTypeWithMethodGet(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "GET /content-type" for route "content_type" returned an internal error.',
        );
    }

    public function testRoutePostRouteWithMethodPost(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "POST /post" for route "post_route" returned an internal error.',
        );
    }

}
