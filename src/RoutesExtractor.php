<?php

namespace Pierstoval\SmokeTesting;

use Symfony\Component\Routing\RouterInterface;

final class RoutesExtractor
{
    public static function extractRoutesFromRouter(RouterInterface $router): \Generator
    {
        foreach ($router->getRouteCollection() as $routeName => $route) {
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
                yield "$method {$routePath}" => ['method' => $method, 'name' => $routeName, 'path' => $routePath];
            }
        }
    }
}
