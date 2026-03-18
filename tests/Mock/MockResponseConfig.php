<?php

namespace Pierstoval\SmokeTesting\Tests\Mock;

final class MockResponseConfig
{
    public function __construct(
        public readonly int $statusCode = 200,
        public readonly string $content = '',
        public readonly array $headers = [],
        public readonly ?string $routeName = null,
    ) {
    }
}
