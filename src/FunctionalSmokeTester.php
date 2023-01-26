<?php

namespace Pierstoval\SmokeTesting;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
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
                sprintf('The "%s" trait can only be used in an instance of "%s"', self::class, WebTestCase::class));
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
            $normalizedHeader = $this->normalizeHttpHeader($header);
            $serverParameters[$normalizedHeader] = $value;
            if ($normalizedHeader === 'HTTP_CONTENT_TYPE') {
                // PHP handles the Content-Type header with the CGI protocol,
                // so we need to de-normalize it to just "CONTENT_TYPE" instead of how PHP would
                // normally do it.
                // @see https://bugs.php.net/bug.php?id=66606&thanks=6
                $serverParameters['CONTENT_TYPE'] = $value;
            }
        }

        foreach ($testData->getServerParameters() as $param => $value) {
            if (isset($serverParameters[$param])) {
                throw new \RuntimeException(sprintf('Parameter "%s" is already defined in server parameters. Have you added it twice as an HTTP header?', $param));
            }
            $serverParameters[$param] = $value;
        }

        if ($callback = $testData->getCallbackBeforeRequest()) {
            \Closure::bind($callback, $this, static::class)($client);
        }

        $crawler = $client->request(
            $testData->getHttpMethod(),
            $testData->getUrl(),
            [],
            [],
            $serverParameters,
            $testData->getRequestPayload(),
        );

        $req = $client->getRequest();
        $res = $client->getResponse();

        $expectedRouteName = $testData->getExpectedRouteName();

        if ($expectedRouteName !== null) {
            $responseRouteName = $req->attributes->get('_route');

            Assert::assertSame($testData->getExpectedRouteName(), $responseRouteName, sprintf(
                'The route name "%s" does not match the expected route name "%s".',
                $responseRouteName,
                $expectedRouteName,
            ));
        }

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

        if ($expectedHeaders = $testData->getExpectedHeaders()) {
            $responseHeaders = $res->headers;
            foreach ($expectedHeaders as $header => $expectedValue) {
                Assert::assertTrue($responseHeaders->has($header), sprintf('Response does not have header "%s".', $header));
                Assert::assertSame($expectedValue, $responseHeaders->get($header));
            }
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

        if ($testData->getIsJsonResponseExpectation()) {
            $header = $res->headers->get('Content-Type');
            Assert::assertMatchesRegularExpression('~application/(ld\+)?json~iU', $header);

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

        if ($callbacks = $testData->getExpectationCallables()) {
            foreach ($callbacks as $callback) {
                $callback = \Closure::fromCallable($callback)->bindTo($this, static::class);
                $callback($client, $crawler);
            }
        }
    }

    private function getHttpClientInternal(string $host = null): KernelBrowser
    {
        static $client;

        if ($client) {
            return $client;
        }

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
