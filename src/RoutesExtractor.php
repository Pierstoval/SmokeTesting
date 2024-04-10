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
                $errorMode = $_SERVER['SMOKE_TESTING_ROUTES_METHODS'] ?? $_ENV['SMOKE_TESTING_ROUTES_METHODS'] ?? getenv('SMOKE_TESTING_ROUTES_METHODS') ?: 'true';

                if (!empty($errorMode) && 'false' !== $errorMode && 'no' !== $errorMode && '0' !== $errorMode && 'disabled' !== $errorMode) {
                    $errorType = E_USER_DEPRECATED;
                    if (str_starts_with($errorMode, 'E_USER_')) {
                        $errorType = constant($errorMode);
                    }
                    $message = sprintf('Route "%s" has no configured HTTP methods. It is recommended that you set at least one HTTP method for your route in its configuration.', $routeName);
                    trigger_error($message, $errorType);
                }

                $methods[] = 'GET';
            }

            foreach ($methods as $method) {
                $routePath = $router->generate($routeName);
                yield "$method {$routePath}" => ['method' => $method, 'name' => $routeName, 'path' => $routePath];
            }
        }
    }
}
