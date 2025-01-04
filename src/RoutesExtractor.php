<?php

namespace Pierstoval\SmokeTesting;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

final class RoutesExtractor
{
    public static function extractRoutesFromRouter(RouterInterface $router): \Generator
    {
        $defaultScheme = $router->getContext()->getScheme();
        $defaultHost = $router->getContext()->getHost();

        foreach ($router->getRouteCollection() as $routeName => $route) {
            /** @var Route $route */
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

                $mustTriggerError = match (\strtolower($errorMode)) {
                    'no', 'false', 'off', 'disabled', '0' => false,
                    default => true,
                };

                if ($mustTriggerError) {
                    $errorType = E_USER_DEPRECATED;
                    $message = sprintf('Route "%s" has no configured HTTP methods. It is recommended that you set at least one HTTP method for your route in its configuration.', $routeName);
                    trigger_error($message, $errorType);
                }

                $methods[] = 'GET';
            }

            $routerReferenceType = UrlGeneratorInterface::ABSOLUTE_PATH;

            $hasDynamicHost = \str_contains($route->getHost(), '{');
            if ($hasDynamicHost) {
                continue;
            }

            $hasDynamicScheme = \array_filter(\array_map(static fn($item) => \str_contains($item, '{'), $route->getSchemes()));
            if ($hasDynamicScheme) {
                continue;
            }

            $hasNonDynamicHost = ($route->getHost() && $route->getHost() !== 'localhost') || ($defaultHost && $defaultHost !== 'localhost');
            $hasNonDynamicScheme = $route->getSchemes() || ($defaultScheme && $defaultScheme !== 'http');

            if ($hasNonDynamicHost || $hasNonDynamicScheme) {
                // Generate full URI if route is configured with a host.
                $routerReferenceType = UrlGeneratorInterface::ABSOLUTE_URL;
            }

            foreach ($methods as $method) {
                $routePath = $router->generate($routeName, [], $routerReferenceType);
                yield "$method {$routePath}" => ['httpMethod' => $method, 'routeName' => $routeName, 'routePath' => $routePath];
            }
        }
    }
}
