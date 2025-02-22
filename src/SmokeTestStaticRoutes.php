<?php

namespace Pierstoval\SmokeTesting;

use PHPUnit\Framework\Attributes\DataProvider;
use Pierstoval\SmokeTesting\PhpUnitVersions\PhpUnit9;
use Pierstoval\SmokeTesting\PhpUnitVersions\PhpUnit12;

use PHPUnit\Runner\Version;

if (((float) Version::series()) < 12) {
    abstract class SmokeTestStaticRoutes extends PhpUnit9
    {
        /**
         * @dataProvider provideRouteCollection
         */
        public function testRoutesDoNotReturnInternalError(string $httpMethod, string $routeName, string $routePath): void
        {
            parent::testRoutesDoNotReturnInternalError($httpMethod, $routeName, $routePath);
        }
    }
} else {
    abstract class SmokeTestStaticRoutes extends PhpUnit12
    {
        #[DataProvider('provideRouteCollection')]
        public function testRoutesDoNotReturnInternalError(string $httpMethod, string $routeName, string $routePath): void
        {
            parent::testRoutesDoNotReturnInternalError($httpMethod, $routeName, $routePath);
        }
    }
}
