<?php

namespace Pierstoval\SmokeTesting\Tests\Mock;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Request as DomRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MockKernelBrowser extends KernelBrowser
{
    /** @var MockResponseConfig[] */
    private array $responseQueue = [];

    private MockResponseConfig $defaultResponse;

    private ?MockResponseConfig $currentConfig = null;

    public function __construct()
    {
        $this->defaultResponse = new MockResponseConfig();

        parent::__construct(new MockKernel());
    }

    public function queueResponse(MockResponseConfig $config): void
    {
        $this->responseQueue[] = $config;
    }

    public function setDefaultResponse(MockResponseConfig $config): void
    {
        $this->defaultResponse = $config;
    }

    protected function filterRequest(DomRequest $request): Request
    {
        $this->currentConfig = \array_shift($this->responseQueue) ?? $this->defaultResponse;

        $httpRequest = parent::filterRequest($request);

        if ($this->currentConfig->routeName !== null) {
            $httpRequest->attributes->set('_route', $this->currentConfig->routeName);
        }

        return $httpRequest;
    }

    protected function doRequest(object $request): Response
    {
        $config = $this->currentConfig ?? $this->defaultResponse;

        return new Response($config->content, $config->statusCode, $config->headers);
    }
}
