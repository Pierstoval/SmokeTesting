<?php

namespace Pierstoval\SmokeTesting;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

trait SmokeTestAllRoutes
{
    /**
     * @return \Generator<string, Route>
     */
    public function provideRouteCollection(): \Generator
    {
        if (!$this instanceof WebTestCase) {
            throw new \Exception(\sprintf('The "%s" trait trait can only be used in an instance of "%s"', self::class, WebTestCase::class));
        }

        static::bootKernel();

        $container = static::getContainer();

        /** @var RouteCollection $routes */
        $routes = $container->get(RouterInterface::class)->getRouteCollection();

        static::ensureKernelShutdown();

        if (!$routes->count()) {
            throw new \RuntimeException('No routes found in the application.');
        }

        foreach ($routes as $name => $route) {
            yield $name => [$name, $route];
        }
    }

    /**
     * @dataProvider provideRouteCollection
     */
    public function testRoutesDoNotReturnHttp500(string $routeName, Route $route): void
    {
        $methods = $route->getMethods();
        if (!$methods) {
            trigger_error(\sprintf("Route %s has no configured HTTP methods. It is recommended that you set at least one HTTP method for your route in its configuration.", $routeName), E_USER_DEPRECATED);

            $methods[] = 'GET';
        }

        $client = static::createClient();

        foreach ($methods as $method) {
            $client->request($method, $route->getPath());

            $response = $client->getResponse();
            static::assertLessThan(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $response->getStatusCode(),
                \sprintf('Route "%s" returned a %d error with HTTP method "%s". Response content: %s', $routeName, $response->getStatusCode(), $method, $response->getContent()),
            );
        }
    }
}
