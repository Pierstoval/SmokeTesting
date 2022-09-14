<?php

namespace Pierstoval\SmokeTesting;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function count;
use function is_array;
use function sprintf;
use function str_replace;
use function strtoupper;
use const JSON_ERROR_NONE;

trait FunctionalSmokeTester
{
    public function runFunctionalTest(FunctionalTestData $testData): void {
        if (!$this instanceof WebTestCase) {
            throw new \Exception(
                sprintf('The "%s" trait trait can only be used in an instance of "%s"', self::class, WebTestCase::class));
        }

        if (!$testData->hasExpectations()) {
            throw new \Exception(
                sprintf(
                'No expectations were provided in the "%s" object. You must provide at least one.',
                FunctionalTestData::class,
            ));
        }

        $client = $this->getHttpClientInternal($testData->getRequestHost());

        $serverParameters = [];

        if ($requestLocale = $testData->getRequestLocale()) {
            $serverParameters['HTTP_ACCEPT_LANGUAGE'] = $requestLocale;
        }

        foreach ($testData->getRequestHeaders() as $header => $value) {
            $serverParameters[$this->normalizeHttpHeader($header)] = $value;
        }

        $crawler = $client->request(
            method: $testData->getHttpMethod(),
            uri: $testData->getUrl(),
            server: $serverParameters,
            content: $testData->getRequestPayload(),
        );

        $req = $client->getRequest();
        $res = $client->getResponse();

        $responseRouteName = $req->attributes->get('_route');

        Assert::assertSame($testData->getExpectedRouteName(), $responseRouteName, sprintf(
            'The route name "%s" does not match the expected route name "%s".',
            $testData->getExpectedRouteName(),
            $responseRouteName
        ));

        if ($expectedStatusCode = $testData->getExpectedStatusCode()) {
            $responseCode = $res->getStatusCode();
            Assert::assertSame($expectedStatusCode, $responseCode, sprintf(
                'Expected HTTP response to return status code "%s", but returned "%s" instead.',
                $expectedStatusCode,
                $responseCode,
            ));
        }

        if ($expectedRedirectUrl = $testData->getExpectedRedirectUrl()) {
            Assert::assertContains($res->getStatusCode(), [201, 301, 302, 303, 307, 308], sprintf(
                'Expected redirect URL, but response status "%s" is not a redirection code.',
                $res->getStatusCode(),
            ));

            $responseRedirectUrl = $res->headers->get('Location');

            Assert::assertSame($expectedRedirectUrl, $responseRedirectUrl, sprintf(
                'Expected redirect URL "%s", got "%s" instead.',
                $expectedRedirectUrl,
                $responseRedirectUrl
            ));
        }

        $expectedCssSelector = $testData->getExpectedCssSelector();

        $crawlerSubElement = null;
        if ($expectedCssSelector) {
            $crawlerSubElement = $crawler->filter($expectedCssSelector);
            Assert::assertGreaterThan(0, $crawlerSubElement->count(), sprintf(
                'Expected CSS selector "%s" to be present, but it was not found.',
                $expectedCssSelector,
            ));
        }

        $callbacks = $testData->getExpectationCallables();
        if ($callbacks) {
            foreach ($callbacks as $callback) {
                $callback = $callback(...)->bindTo($this, static::class);
                $callback($client, $crawler);
            }
        }

        if ($testData->getIsJsonResponseExpectation()) {
            $header = $res->headers->get('Content-Type');
            Assert::assertMatchesRegularExpression('~^application/(ld\+)?json$~iU', $header);

            $resBody = $res->getContent();
            $json = @json_decode($resBody, true);
            $lastError = json_last_error();
            Assert::assertSame(JSON_ERROR_NONE, $lastError, 'There was a JSON error of code '.$lastError);

            if ($jsonParts = $testData->getExpectedJsonParts()) {
                $this->applyJsonPartsExpectation($jsonParts, $json);
            }
        }

        $expectedText = $testData->getExpectedText();

        if ($expectedText) {
            $textToMatch = $expectedCssSelector ? $crawlerSubElement->text() : $res->getContent();

            Assert::assertStringContainsString($expectedText, $textToMatch);
        }

        if ($callbacks) {
            foreach ($callbacks as $callback) {
                $callback = $callback(...)->bindTo($this, static::class);
                $callback($client, $crawler);
            }
        }
    }

    private function getHttpClientInternal(string $host = null): KernelBrowser
    {
        $server = [];

        if ($host) {
            $server['HTTP_HOST'] = $host;
        }

        $client = self::createClient([], $server);
        // Disable reboot, allows client to be reused for other requests.
        $client->disableReboot();

        return $client;
    }

    private function applyJsonPartsExpectation(array $jsonParts, array $json): void
    {
        foreach ($jsonParts as $key => $part) {
            Assert::assertArrayHasKey($key, $json);
            $subJson = $json[$key];
            if (is_array($part)) {
                $this->applyJsonPartsExpectation($part, $subJson);
            } else {
                Assert::assertSame($part, $subJson);
            }
        }
    }

    private function normalizeHttpHeader(string $header): string
    {
        return 'HTTP_'.strtoupper(str_replace('-', '_', $header));
    }
}
