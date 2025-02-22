<?php

namespace Pierstoval\SmokeTesting\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Pierstoval\SmokeTesting\RoutesExtractor;
use Symfony\Component\Routing\Loader\ClosureLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class RoutesExtractorTest extends TestCase
{
    public function test_basic_get_route(): void
    {
        $router = $this->getRouter(function () {
            $c = new RouteCollection();
            $c->add('app', new Route('/'));
            return $c;
        });

        $routes = \iterator_to_array(RoutesExtractor::extractRoutesFromRouter($router));

        self::assertSame([
            "GET /" => ["httpMethod" => "GET", "routeName" => "app", "routePath" => "/"],
        ], $routes);
    }

    public function test_basic_post_route(): void
    {
        $router = $this->getRouter(function () {
            $c = new RouteCollection();
            $c->add('app', new Route('/', methods: 'POST'));
            return $c;
        });

        $routes = \iterator_to_array(RoutesExtractor::extractRoutesFromRouter($router));

        self::assertSame([
            "POST /" => ["httpMethod" => "POST", "routeName" => "app", "routePath" => "/"],
        ], $routes);
    }

    public function test_basic_multiple_methods_route(): void
    {
        $router = $this->getRouter(function () {
            $c = new RouteCollection();
            $c->add('app', new Route('/', methods: ['GET', 'POST', 'DELETE']));
            return $c;
        });

        $routes = \iterator_to_array(RoutesExtractor::extractRoutesFromRouter($router));

        self::assertSame([
            "GET /" => ["httpMethod" => "GET", "routeName" => "app", "routePath" => "/"],
            "POST /" => ["httpMethod" => "POST", "routeName" => "app", "routePath" => "/"],
            "DELETE /" => ["httpMethod" => "DELETE", "routeName" => "app", "routePath" => "/"],
        ], $routes);
    }

    /**
     * @dataProvider provideDynamicRoutes
     */
    #[DataProvider('provideDynamicRoutes')]
    public function test_dynamic_routes_are_not_included(string $routeName, Route $route): void
    {
        $router = $this->getRouter(function () use ($routeName, $route) {
            $c = new RouteCollection();
            $c->add($routeName, $route);
            return $c;
        });

        $routes = \iterator_to_array(RoutesExtractor::extractRoutesFromRouter($router));

        self::assertSame([], $routes);
    }

    public static function provideDynamicRoutes(): \Generator
    {
        yield ['app1', new Route('/1', host: '{host}')];
        yield ['app2', new Route('/2', schemes: '{scheme}')];
        yield ['app3', new Route('/3/{data}')];
    }

    public function test_router_with_request_context(): void
    {
        $router = $this->getRouter(function () {
            $c = new RouteCollection();
            $c->add('app1', new Route('/', methods: ['GET']));
            return $c;
        });
        $router->setContext(new RequestContext(host: 'test.localhost'));

        $routes = \iterator_to_array(RoutesExtractor::extractRoutesFromRouter($router));

        self::assertSame([
            "GET http://test.localhost/" => ["httpMethod" => "GET", "routeName" => "app1", "routePath" => "http://test.localhost/"],
        ], $routes);
    }

    public function test_framework_router_with_base_url_option(): void
    {
        $router = $this->getRouter(function () {
            $c = new RouteCollection();
            $c->add('app1', new Route('/', methods: ['GET']));
            return $c;
        });
        $router->setContext(new RequestContext(baseUrl: 'http://something.localhost'));

        $routes = \iterator_to_array(RoutesExtractor::extractRoutesFromRouter($router));

        self::assertSame([
            "GET http://something.localhost/" => ["httpMethod" => "GET", "routeName" => "app1", "routePath" => "http://something.localhost/"],
        ], $routes);
    }

    private function getRouter(?\Closure $load = null): RouterInterface
    {
        if (!$load) {
            $load = static fn() => new RouteCollection();
        }

        $loader = new ClosureLoader();

        return new Router($loader, $load);
    }
}
