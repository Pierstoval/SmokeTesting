<?php

namespace Pierstoval\SmokeTesting\PhpUnitVersions;

use PHPUnit\Framework\Attributes\DataProvider;
use Pierstoval\SmokeTesting\RoutesExtractor;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use function is_a;
use function sprintf;
use Generator;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

abstract class PhpUnit12 extends WebTestCase
{
    protected function beforeRequest(KernelBrowser $client, string $routeName, string $routePath): void
    {
        // To be overriden by the end-user.
    }

    protected function afterRequest(KernelBrowser $client, string $routeName, string $routePath): void
    {
        // To be overriden by the end-user.
    }

    protected function afterAssertion(KernelBrowser $client, string $routeName, string $routePath): void
    {
        // To be overriden by the end-user.
    }

    /**
     * @return Generator<string, Route>
     */
    public static function provideRouteCollection(): Generator
    {
        if (!is_a(self::class, WebTestCase::class, true)) {
            throw new RuntimeException(sprintf('The "%s" trait trait can only be used in an instance of "%s"', self::class, WebTestCase::class));
        }

        static::bootKernel();

        /** @var TestContainer $container */
        $container = static::getContainer();

        /** @var RouterInterface $router */
        $router = $container->get(RouterInterface::class);

        $routes = $router->getRouteCollection();

        static::ensureKernelShutdown();

        if (!$routes->count()) {
            throw new RuntimeException('No routes found in the application.');
        }

        yield from RoutesExtractor::extractRoutesFromRouter($router);
    }

    #[DataProvider('provideRouteCollection')]
    public function testRoutesDoNotReturnInternalError(string $httpMethod, string $routeName, string $routePath): void
    {
        $client = static::createClient();

        $this->beforeRequest($client, $routeName, $routePath);

        $client->request($httpMethod, $routePath);

        $this->afterRequest($client, $routeName, $routePath);

        $response = $client->getResponse();
        static::assertLessThan(
            500,
            $response->getStatusCode(),
            sprintf('Request "%s %s" for route "%s" returned an internal error.', $httpMethod, $routePath, $routeName),
        );

        $this->afterAssertion($client, $routeName, $routePath);
    }
}
