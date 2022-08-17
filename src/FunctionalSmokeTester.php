<?php

namespace Pierstoval\SmokeTesting;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

trait FunctionalSmokeTester
{
    public function runFunctionalTest(FunctionalTestData $testData): void {
        if (!$this instanceof WebTestCase) {
            throw new \Exception(\sprintf('The "%s" trait trait can only be used in an instance of "%s"', self::class, WebTestCase::class));
        }

        if (!$testData->hasExpectations()) {
            throw new \Exception(\sprintf(
                'No expectations were provided in the "%s" object. You must provide at least one.',
                FunctionalTestData::class,
            ));
        }

        $client = $this->getHttpClientInternal($testData->getRequestHost());

        $serverParameters = [];
        if ($requestLocale = $testData->getRequestLocale()) {
            $serverParameters['HTTP_ACCEPT_LANGUAGE'] = $requestLocale;
        }

        $crawler = $client->request(
            method: strtoupper($testData->getHttpMethod()),
            uri: $testData->getUrl(),
            server: $serverParameters,
            content: $testData->getRequestPayload(),
        );

        $req = $client->getRequest();
        $res = $client->getResponse();

        $responseRouteName = $req->attributes->get('_route');

        Assert::assertSame($testData->getExpectedRouteName(), $responseRouteName, \sprintf(
            'The route name "%s" does not match the expected route name "%s".',
            $testData->getExpectedRouteName(),
            $responseRouteName
        ));

        if ($expectedStatusCode = $testData->getExpectedStatusCode()) {
            $responseCode = $res->getStatusCode();
            Assert::assertSame($expectedStatusCode, $responseCode, \sprintf(
                'Expected HTTP response to return status code "%s", but returned "%s" instead.',
                $expectedStatusCode,
                $responseCode,
            ));
        }

        if ($expectedRedirectUrl = $testData->getExpectedRedirectUrl()) {
            Assert::assertContains($res->getStatusCode(), [201, 301, 302, 303, 307, 308], \sprintf(
                'Expected redirect URL, but response status "%s" is not a redirection code.',
                $res->getStatusCode(),
            ));

            $responseRedirectUrl = $res->headers->get('Location');

            Assert::assertSame($expectedRedirectUrl, $responseRedirectUrl, \sprintf(
                'Expected redirect URL "%s", got "%s" instead.',
                $expectedRedirectUrl,
                $responseRedirectUrl
            ));
        }

        $expectedCssSelector = $testData->getExpectedCssSelector();

        $crawlerSubElement = null;
        if ($expectedCssSelector) {
            $crawlerSubElement = $crawler->filter($expectedCssSelector);
            Assert::assertGreaterThan(0, $crawlerSubElement->count(), \sprintf(
                'Expected CSS selector "%s" to be present, but it was not found.',
                $expectedCssSelector,
            ));
        }

        $expectedText = $testData->getExpectedText();

        if ($expectedText) {
            $textToMatch = $expectedCssSelector ? $crawlerSubElement->text() : $res->getContent();

            Assert::assertStringContainsString($expectedText, $textToMatch);
        }
    }

    private function getHttpClientInternal(string $host = null): KernelBrowser
    {
        if (!$this instanceof WebTestCase) {
            throw new \RuntimeException(\sprintf('Test case must extend %s to use Kernel features', KernelTestCase::class));
        }

        $server = [];

        if ($host) {
            $server['HTTP_HOST'] = $host;
        }

        /** @var KernelBrowser $client */
        $client = self::createClient([], $server);
        // Disable reboot, allows client to be reused for other requests.
        $client->disableReboot();

        return $client;
    }
}
