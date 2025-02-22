<?php

namespace App\Tests;

use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FunctionalSmokeTest extends WebTestCase
{
    use FunctionalSmokeTester;

    public function testGetParameterWithoutDefault(): void
    {
        try {
            $this->runFunctionalTest(
                FunctionalTestData::withUrl("/param")
                    ->expectStatusCode(404),
            );
            $this->runFunctionalTest(
                FunctionalTestData::withUrl("/param/")
                    ->expectStatusCode(404),
            );
        } catch (NotFoundHttpException) {
            $this->assertTrue(true);
        }
    }

    public function testGetParameterWithDefault(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl("/other_param")
                ->expectStatusCode(200)
                ->expectTextToBePresent('Content: default_value')
        );
        $this->runFunctionalTest(
            FunctionalTestData::withUrl("/other_param/other_value")
                ->expectStatusCode(200)
                ->expectTextToBePresent('Content: other_value')
        );
    }

    public function testGet200(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/200')
                ->expectRouteName('get_200')
                ->expectStatusCode(200)
                ->expectTextToBePresent('200')
        );
    }

    public function testGet400(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/400')
                ->expectRouteName('get_400')
                ->expectStatusCode(400)
                ->expectTextToBePresent('400')
        );
    }

    public function testGet500(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/500')
                ->expectRouteName('get_500')
                ->expectStatusCode(500)
                ->expectTextToBePresent('500')
        );
    }

    public function testGetWithEmptyPayload(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/payload')
                ->withPayload('')
                ->expectRouteName('get_with_payload')
                ->expectStatusCode(400)
                ->expectTextToBePresent('')
        );
    }

    public function testGetWithPayload(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/payload')
                ->withPayload($payload = 'My payload')
                ->expectRouteName('get_with_payload')
                ->expectStatusCode(200)
                ->expectTextToBePresent($payload)
        );
    }

    public function testGetWithValidJson(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/valid')
                ->expectRouteName('json_valid')
                ->expectStatusCode(200)
                ->expectJsonParts([
                    'message' => 'Ok!',
                    'code' => 200,
                ])
        );
    }

    public function testGetWithValidJsonHeader(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/valid-header')
                ->expectRouteName('json_valid_header')
                ->expectStatusCode(200)
                ->expectJsonParts([
                    'message' => 'Ok!',
                    'code' => 200,
                ])
        );
    }

    public function testGetWithMissingJsonResponseHeader(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/missing_header')
                ->expectRouteName('json_missing_header')
                ->expectStatusCode(200)
                ->expectTextToBePresent('{"message":"I miss the JSON response header!","code":200}')
                ->expectIsJsonResponse()
        );
    }

    public function testGetWithInvalidJson(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/json/invalid')
                ->expectRouteName('json_invalid')
                ->expectStatusCode(200)
                ->expectTextToBePresent('{"message":')
                ->expectIsJsonResponse()
        );
    }

    public function testGetWithPreRequestCallback(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/cookie/value')
                ->withCallbackBeforeRequest(function (KernelBrowser $browser): void {
                    $browser->getCookieJar()->set(new Cookie('test_cookie', 'test value'));
                })
                ->expectRouteName('cookie_value')
                ->expectStatusCode(200)
                ->expectTextToBePresent('Value: "test value"')
        );
    }

    public function testWithFixedHost(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('http://test.localhost/host/fixed')
                ->expectRouteName('host_fixed')
                ->expectStatusCode(200)
                ->expectTextToBePresent('Value: "test.localhost"')
        );
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/host/fixed')
                ->expectRouteName('host_fixed')
                ->expectStatusCode(200)
                ->expectTextToBePresent('Value: "test.localhost"')
        );
    }

    public function testGetWithContentType(): void
    {
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/content-type')
                ->withHttpHeader('Content-Type', 'application/ld+json; charset=utf-8')
                ->expectRouteName('content_type')
                ->expectStatusCode(200)
                ->expectJsonParts([
                    'header' => 'application/ld+json; charset=utf-8',
                    'server_normalized' => 'application/ld+json; charset=utf-8',
                    'server_denormalized' => 'application/ld+json; charset=utf-8',
                    'format' => 'jsonld',
                ])
        );
    }
}
