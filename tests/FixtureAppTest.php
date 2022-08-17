<?php

namespace Pierstoval\SmokeTesting\Tests;

use Symfony\Component\Process\Process;

class FixtureAppTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return string The test process output.
     */
    private function runFixtureTest(string $testsCasesFilter): Process
    {
        $fixtureAppDir = \dirname(__DIR__).'/fixture-app';
        $phpunitPath = $fixtureAppDir.'/vendor/bin/phpunit';

        $phpunitCommand = [
            PHP_BINARY,
            $phpunitPath,
            '--color=never',
            '--testdox',
            '--filter='.$testsCasesFilter,
        ];

        $testProcess = new Process($phpunitCommand, $fixtureAppDir, timeout: 5);

        $testProcess->start();

        sleep(3);

        $testProcess->stop();

        return $testProcess;
    }

    public static function provideSuccessfulRoutes(): \Generator
    {
        yield 'get_200' => ['get_200'];
        yield 'get_400' => ['get_400'];
    }

    /**
     * @dataProvider provideSuccessfulRoutes
     */
    public function testGetOk(string $routeName): void
    {
        $testProcess = $this->runFixtureTest($routeName);
        $stdout = $testProcess->getOutput();

        self::assertStringContainsString(sprintf("✔ Routes do not return http 500 with data set \"%s\"", $routeName), $stdout);
        self::assertStringContainsString(\sprintf("1x: Route %s has no configured HTTP methods. It is recommended that you set at least one HTTP method for your route in its configuration.", $routeName), $stdout);
    }

    public function testGet500(): void
    {
        $routeName = 'get_500';
        $testProcess = $this->runFixtureTest($routeName);
        $stdout = $testProcess->getOutput();

        self::assertStringContainsString(sprintf("✘ Routes do not return http 500 with data set \"%s\"", $routeName), $stdout);
        self::assertStringContainsString(\sprintf("1x: Route %s has no configured HTTP methods. It is recommended that you set at least one HTTP method for your route in its configuration.", $routeName), $stdout);
    }

    public function testFunctionalGet200(): void
    {
        $testProcess = $this->runFixtureTest('FunctionalSmokeTest::testGet200');
        $stdout = $testProcess->getOutput();

        self::assertStringContainsString('✔ Get 200', $stdout);
        self::assertStringContainsString('OK (1 test, 3 assertions)', $stdout);
    }

    public function testFunctionalGetWithEmptyPayload(): void
    {
        $testProcess = $this->runFixtureTest('FunctionalSmokeTest::testGetWithEmptyPayload');
        $stdout = $testProcess->getOutput();

        self::assertStringContainsString('✔ Get with empty payload', $stdout);
        self::assertStringContainsString('OK (1 test, 2 assertions)', $stdout);
    }

    public function testFunctionalGetWithPayload(): void
    {
        $testProcess = $this->runFixtureTest('FunctionalSmokeTest::testGetWithPayload');
        $stdout = $testProcess->getOutput();

        self::assertStringContainsString('✔ Get with payload', $stdout);
        self::assertStringContainsString('OK (1 test, 3 assertions)', $stdout);
    }
}
