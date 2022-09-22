<?php

namespace Pierstoval\SmokeTesting;

use function count;

class FunctionalTestData {
    // Request information
    private readonly string $url;
    private ?string $withHost = null;
    private ?string $withLocale = null;
    private string $withMethod = 'GET';
    private array $withHttpHeaders = [];
    private array $withServerParameters = [];
    private ?string $withPayload = null;

    // Expectations
    private ?string $expectRouteName = null;
    private ?int $expectStatusCode = null;
    private ?string $expectRedirectUrl = null;
    private ?string $expectCssSelector = null;
    private ?string $expectText = null;
    private bool $expectIsJsonResponse = false;
    /** @var array<callable> */
    private array $expectationCallables = [];
    /** @var array<string, string> */
    private array $expectedHeaders = [];
    private ?array $expectJsonParts = null;

    private function __construct(string $url)
    {
        $this->url = $url;
    }

    public function hasExpectations(): bool
    {
        return $this->expectRouteName !== null
            || $this->expectStatusCode !== null
            || $this->expectRedirectUrl !== null
            || $this->expectCssSelector !== null
            || $this->expectText !== null
            || $this->expectIsJsonResponse !== false
            || count($this->expectationCallables) > 0
            || count($this->expectedHeaders) > 0
            || $this->expectJsonParts !== null
        ;
    }

    public static function withUrl(string $url): self
    {
        return new self($url);
    }

    public function withHost(string $host): self
    {
        $new = clone $this;
        $new->withHost = $host;

        return $new;
    }

    public function withMethod(string $method): self
    {
        $new = clone $this;
        $new->withMethod = strtoupper($method);

        return $new;
    }

    public function withPayload(string $payload): self
    {
        $new = clone $this;
        $new->withPayload = $payload;

        return $new;
    }

    public function withUserLocale(string $locale): self
    {
        $new = clone $this;
        $new->withLocale = $locale;

        return $new;
    }

    public function withHttpHeader(string $name, string $value): self
    {
        $new = clone $this;
        $new->withHttpHeaders[$name] = $value;

        return $new;
    }

    public function withServerParameters(string $name, string $value): self
    {
        $new = clone $this;
        if (isset($new->withServerParameters[$name])) {
            throw new \RuntimeException(sprintf('Request header "%s" is already defined in test data. Have you added it twice?', $name));
        }
        $new->withServerParameters[$name] = $value;

        return $new;
    }

    public function expectRouteName(string $routeName): self
    {
        $new = clone $this;
        $new->expectRouteName = $routeName;

        return $new;
    }

    public function expectStatusCode(int $statusCode): self {
        $new = clone $this;
        $new->expectStatusCode = $statusCode;

        return $new;
    }

    public function expectRedirectUrl(string $redirectUrl): self
    {
        $new = clone $this;
        $new->expectRedirectUrl = $redirectUrl;

        return $new;
    }

    public function expectTextToBePresent(string $text): self
    {
        $new = clone $this;
        $new->expectText = $text;

        return $new;
    }

    public function expectCssSelectorToContainText(string $cssSelector, string $text): self
    {
        return $this
            ->expectCssSelectorToBePresent($cssSelector)
            ->expectTextToBePresent($text)
        ;
    }

    public function expectCssSelectorToBePresent(string $cssSelector): self
    {
        $new = clone $this;
        $new->expectCssSelector = $cssSelector;

        return $new;
    }

    public function appendCallableExpectation(callable $callable): self
    {
        $new = clone $this;
        $new->expectationCallables[] = $callable;

        return $new;
    }

    public function expectResponseHeader(string $headerName, string $headerValue): self
    {
        $new = clone $this;
        if (isset($new->expectedHeaders[$headerName])) {
            throw new \RuntimeException(sprintf('Expected header "%s" is already defined in test expectations. Have you added it twice?', $headerName));
        }
        $new->expectedHeaders[$headerName] = $headerValue;

        return $new;
    }

    public function expectIsJsonResponse(): self
    {
        $new = clone $this;
        $new->expectIsJsonResponse = true;

        return $new;
    }

    public function expectJsonParts(array $jsonParts): self
    {
        $new = clone $this->expectIsJsonResponse();
        $new->expectJsonParts = $jsonParts;

        return $new;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getRequestHost(): ?string
    {
        return $this->withHost;
    }

    public function getRequestLocale(): ?string
    {
        return $this->withLocale;
    }

    public function getHttpMethod(): string
    {
        return $this->withMethod;
    }

    public function getRequestHeaders(): array
    {
        return $this->withHttpHeaders;
    }

    public function getServerParameters(): array
    {
        return $this->withServerParameters;
    }

    public function getRequestPayload(): ?string
    {
        return $this->withPayload;
    }

    public function getExpectedRouteName(): ?string
    {
        return $this->expectRouteName;
    }

    public function getExpectedStatusCode(): ?int
    {
        return $this->expectStatusCode;
    }

    public function getExpectedRedirectUrl(): ?string
    {
        return $this->expectRedirectUrl;
    }

    public function getExpectedCssSelector(): ?string
    {
        return $this->expectCssSelector;
    }

    public function getExpectedText(): ?string
    {
        return $this->expectText;
    }

    public function getExpectationCallables(): array
    {
        return $this->expectationCallables;
    }

    public function getIsJsonResponseExpectation(): bool
    {
        return $this->expectIsJsonResponse;
    }

    public function getExpectedHeaders(): array
    {
        return $this->expectedHeaders;
    }

    public function getExpectedJsonParts(): ?array
    {
        return $this->expectJsonParts;
    }
}
