<?php

namespace Pierstoval\SmokeTesting;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use function count;
use function sprintf;
use Generator;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

abstract class SmokeTestStaticRoutes extends WebTestCase
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
    public function provideRouteCollection(): Generator
    {
        if (!$this instanceof WebTestCase) {
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

        foreach ($routes as $routeName => $route) {
            $compiledRoute = $route->compile();
            $variables = $compiledRoute->getVariables();
            if (count($variables) > 0) {
                $defaults = $route->getDefaults();
                $defaultsKeys = array_keys($defaults);
                $diff = array_diff($variables, $defaultsKeys);
                if (count($diff) > 0) {
                    // Dynamic route with no defaults, won't handle it
                    continue;
                }
            }

            $methods = $route->getMethods();
            if (!$methods) {
                trigger_error(sprintf("Route %s has no configured HTTP methods. It is recommended that you set at least one HTTP method for your route in its configuration.", $routeName), E_USER_DEPRECATED);

                $methods[] = 'GET';
            }

            foreach ($methods as $method) {
                $routePath = $router->generate($routeName);
                yield "$method {$routePath}" => [$method, $routeName, $routePath];
            }
        }
    }

    /**
     * @dataProvider provideRouteCollection
     *
     * @test
     */
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
