<?php

namespace Pierstoval\SmokeTesting\Tests;

use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use function dirname;
use function sprintf;

class FixtureAppTest extends TestCase
{
    private function runFixtureTest(string $testsCasesFilter): Process
    {
        $fixtureAppDir = dirname(__DIR__).'/fixture-app';
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

    public static function provideSuccessfulRoutes(): Generator
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
        self::assertStringContainsString(
            sprintf("1x: Route %s has no configured HTTP methods. It is recommended that you set at least one HTTP method for your route in its configuration.", $routeName), $stdout);
    }

    public function testGet500(): void
    {
        $routeName = 'get_500';
        $testProcess = $this->runFixtureTest($routeName);
        $stdout = $testProcess->getOutput();

        self::assertStringContainsString(sprintf("✘ Routes do not return http 500 with data set \"%s\"", $routeName), $stdout);
        self::assertStringContainsString(
            sprintf("1x: Route %s has no configured HTTP methods. It is recommended that you set at least one HTTP method for your route in its configuration.", $routeName), $stdout);
        self::assertStringContainsString("FAILURES!\nTests: 1, Assertions: 1, Failures: 1.", $stdout);
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

    public function testFunctionalGetWithValidJson(): void
    {
        $testProcess = $this->runFixtureTest('FunctionalSmokeTest::testGetWithValidJson');
        $stdout = $testProcess->getOutput();

        self::assertStringContainsString('✔ Get with valid json', $stdout);
        self::assertStringContainsString('OK (1 test, 8 assertions)', $stdout);
    }

    public function testFunctionalGetWithMissingJsonHeader(): void
    {
        $testProcess = $this->runFixtureTest('FunctionalSmokeTest::testGetWithMissingJsonResponseHeader');
        $stdout = $testProcess->getOutput();

        self::assertStringContainsString('✘ Get with missing json response header', $stdout);
        self::assertStringContainsString('Failed asserting that \'text/html; charset=UTF-8\' matches PCRE pattern "~^application/(ld\+)?json$~iU".', $stdout);
        self::assertStringContainsString("FAILURES!\nTests: 1, Assertions: 3, Failures: 1", $stdout);
    }

    public function testFunctionalGetInvalidJson(): void
    {
        $testProcess = $this->runFixtureTest('FunctionalSmokeTest::testGetWithInvalidJson');
        $stdout = $testProcess->getOutput();

        self::assertStringContainsString('✘ Get with invalid json', $stdout);
        self::assertStringContainsString('here was a JSON error of code 4', $stdout);
        self::assertStringContainsString('Failed asserting that 4 is identical to 0.', $stdout);
        self::assertStringContainsString("FAILURES!\nTests: 1, Assertions: 4, Failures: 1", $stdout);
    }
}
