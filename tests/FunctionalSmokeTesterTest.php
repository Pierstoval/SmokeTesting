<?php

namespace Pierstoval\SmokeTesting\Tests;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\ExpectationFailedException;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Pierstoval\SmokeTesting\Tests\Mock\MockKernelBrowser;
use Pierstoval\SmokeTesting\Tests\Mock\MockResponseConfig;
use Pierstoval\SmokeTesting\Tests\Mock\MockWebTestCase;

class FunctionalSmokeTesterTest extends MockWebTestCase
{
    private static MockKernelBrowser $sharedBrowser;

    protected function setUp(): void
    {
        if (!isset(self::$sharedBrowser)) {
            self::$sharedBrowser = new MockKernelBrowser();
        }
        self::setMockClient(self::$sharedBrowser);
    }

    public function testNoExpectationsThrowsException(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No expectations were provided');

        $this->runFunctionalTest(FunctionalTestData::withUrl('/test'));
    }


    public function testStatusCodePass(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig(statusCode: 200));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectStatusCode(200)
        );
    }

    public function testStatusCodeFail(): void
    {
        $this->expectException(ExpectationFailedException::class);

        self::$sharedBrowser->queueResponse(new MockResponseConfig(statusCode: 404));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectStatusCode(200)
        );
    }


    public function testRouteNamePass(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig(routeName: 'my_route'));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectRouteName('my_route')
        );
    }

    public function testRouteNameFail(): void
    {
        $this->expectException(ExpectationFailedException::class);

        self::$sharedBrowser->queueResponse(new MockResponseConfig(routeName: 'other'));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectRouteName('my_route')
        );
    }


    public function testRedirectUrlPass(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            statusCode: 302,
            headers: ['Location' => '/destination'],
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->expectStatusCode(302)
                ->expectRedirectUrl('/destination')
        );
    }

    public function testRedirectUrlFailNonRedirectStatus(): void
    {
        $this->expectException(ExpectationFailedException::class);

        self::$sharedBrowser->queueResponse(new MockResponseConfig(statusCode: 200));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectRedirectUrl('/destination')
        );
    }

    public function testRedirectUrlFailWrongUrl(): void
    {
        $this->expectException(ExpectationFailedException::class);

        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            statusCode: 302,
            headers: ['Location' => '/wrong'],
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectRedirectUrl('/destination')
        );
    }


    public function testResponseHeaderPass(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            headers: ['X-Custom' => 'value'],
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectResponseHeader('X-Custom', 'value')
        );
    }

    public function testResponseHeaderFail(): void
    {
        $this->expectException(ExpectationFailedException::class);

        self::$sharedBrowser->queueResponse(new MockResponseConfig());

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectResponseHeader('X-Missing', 'value')
        );
    }


    public function testCssSelectorPass(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            content: '<html><body><div class="target">Content</div></body></html>',
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectCssSelectorToBePresent('.target')
        );
    }

    public function testCssSelectorFail(): void
    {
        $this->expectException(ExpectationFailedException::class);

        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            content: '<html><body><p>No target</p></body></html>',
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectCssSelectorToBePresent('.target')
        );
    }


    public function testTextInBody(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            content: 'Hello World',
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectTextToBePresent('Hello')
        );
    }

    public function testTextInCssElement(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            content: '<html><body><div class="t">Found</div></body></html>',
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->expectCssSelectorToContainText('.t', 'Found')
        );
    }


    public function testJsonResponsePass(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            content: '{"key": "value"}',
            headers: ['Content-Type' => 'application/json'],
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectIsJsonResponse()
        );
    }

    public function testJsonPartsPass(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            content: '{"outer": {"inner": "value"}}',
            headers: ['Content-Type' => 'application/json'],
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->expectJsonParts(['outer' => ['inner' => 'value']])
        );
    }

    public function testJsonPartsFail(): void
    {
        $this->expectException(ExpectationFailedException::class);

        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            content: '{"key": "value"}',
            headers: ['Content-Type' => 'application/json'],
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->expectJsonParts(['missing' => 'value'])
        );
    }

    public function testInvalidJsonFail(): void
    {
        $this->expectException(ExpectationFailedException::class);

        self::$sharedBrowser->queueResponse(new MockResponseConfig(
            content: 'not json',
            headers: ['Content-Type' => 'application/json'],
        ));

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')->expectIsJsonResponse()
        );
    }


    public function testCallableExpectation(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig());

        $callbackCalled = false;

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->appendCallableExpectation(function ($client, $crawler) use (&$callbackCalled) {
                    $callbackCalled = true;
                    $this->assertInstanceOf(MockKernelBrowser::class, $client);
                    $this->assertNotNull($crawler);
                })
        );

        $this->assertTrue($callbackCalled);
    }


    public function testHttpMethod(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig());

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->withMethod('POST')
                ->appendCallableExpectation(function ($client) {
                    $this->assertSame('POST', $client->getRequest()->getMethod());
                })
        );
    }

    public function testLocale(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig());

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->withUserLocale('fr')
                ->appendCallableExpectation(function ($client) {
                    $this->assertSame('fr', $client->getRequest()->server->get('HTTP_ACCEPT_LANGUAGE'));
                })
        );
    }

    public function testCustomHttpHeaders(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig());

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->withHttpHeader('X-Custom-Header', 'custom-value')
                ->appendCallableExpectation(function ($client) {
                    $this->assertSame('custom-value', $client->getRequest()->server->get('HTTP_X_CUSTOM_HEADER'));
                })
        );
    }

    public function testContentTypeHeader(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig());

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->withHttpHeader('Content-Type', 'application/json')
                ->appendCallableExpectation(function ($client) {
                    $this->assertSame('application/json', $client->getRequest()->server->get('HTTP_CONTENT_TYPE'));
                    $this->assertSame('application/json', $client->getRequest()->server->get('CONTENT_TYPE'));
                })
        );
    }

    public function testServerParameters(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig());

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->withServerParameters('CUSTOM_PARAM', 'custom-value')
                ->appendCallableExpectation(function ($client) {
                    $this->assertSame('custom-value', $client->getRequest()->server->get('CUSTOM_PARAM'));
                })
        );
    }

    public function testDuplicateServerParamThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is already defined in server parameters');

        self::$sharedBrowser->queueResponse(new MockResponseConfig());

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->withHttpHeader('Accept', 'text/html')
                ->withServerParameters('HTTP_ACCEPT', 'text/html')
                ->expectStatusCode(200)
        );
    }

    public function testCallbackBeforeRequest(): void
    {
        self::$sharedBrowser->queueResponse(new MockResponseConfig());

        $callbackCalled = false;
        $capturedThis = null;
        $capturedClient = null;

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->withCallbackBeforeRequest(function ($client) use (&$callbackCalled, &$capturedThis, &$capturedClient) {
                    $callbackCalled = true;
                    $capturedThis = $this;
                    $capturedClient = $client;
                })
                ->expectStatusCode(200)
        );

        $this->assertTrue($callbackCalled);
        $this->assertSame($this, $capturedThis);
        $this->assertInstanceOf(MockKernelBrowser::class, $capturedClient);
    }

    #[RunInSeparateProcess]
    public function testRequestHost(): void
    {
        $browser = new MockKernelBrowser();
        $browser->queueResponse(new MockResponseConfig());
        self::setMockClient($browser);

        $this->runFunctionalTest(
            FunctionalTestData::withUrl('/test')
                ->withHost('example.com')
                ->appendCallableExpectation(function ($client) {
                    $this->assertSame('example.com', $client->getRequest()->getHost());
                })
        );
    }
}
