<?php

namespace Pierstoval\SmokeTesting\Tests\Mock;

use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MockWebTestCase extends WebTestCase
{
    use FunctionalSmokeTester;

    private static ?MockKernelBrowser $mockClient = null;

    public static function setMockClient(MockKernelBrowser $client): void
    {
        self::$mockClient = $client;
    }

    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        if (self::$mockClient === null) {
            throw new \LogicException('MockWebTestCase requires a mock client. Call setMockClient() before creating a client.');
        }

        $client = self::$mockClient;
        $client->setServerParameters($server);

        return self::getClient($client);
    }

    protected static function getKernelClass(): string
    {
        return MockKernel::class;
    }

    protected function tearDown(): void
    {
        self::getClient(null);
        self::$mockClient = null;
    }
}
