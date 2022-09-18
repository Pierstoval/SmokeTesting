<?php

namespace Pierstoval\SmokeTesting\Tests;

use App\Tests\FunctionalSmokeTest;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;

require_once __DIR__.'/../fixture-app/tests/bootstrap.php';

class FixtureAppTest extends TestCase
{
    private function runFixtureTest(string $testMethod): TestResult
    {
        $testResult = (new FunctionalSmokeTest($testMethod))->run();

        self::assertSame(1, $testResult->count());

        return $testResult;
    }

    public function testGet200(): void
    {
        $testResult = $this->runFixtureTest('testGet200');
        self::assertTrue($testResult->wasSuccessful());
    }

    public function testGet400(): void
    {
        $testResult = $this->runFixtureTest('testGet400');
        self::assertTrue($testResult->wasSuccessful());
    }

    public function testGet500(): void
    {
        $testResult = $this->runFixtureTest('testGet500');
        self::assertTrue($testResult->wasSuccessful());
    }

    public function testGetParameterWithoutDefault(): void
    {
        $testResult = $this->runFixtureTest('testGetParameterWithoutDefault');
        self::assertTrue($testResult->wasSuccessful());
    }

    public function testGetParameterWithDefault(): void
    {
        $testResult = $this->runFixtureTest('testGetParameterWithDefault');
        self::assertTrue($testResult->wasSuccessful());
    }

    public function testGetWithEmptyPayload(): void
    {
        $testResult = $this->runFixtureTest('testGetWithEmptyPayload');
        self::assertTrue($testResult->wasSuccessful());
    }

    public function testGetWithPayload(): void
    {
        $testResult = $this->runFixtureTest('testGetWithPayload');
        self::assertTrue($testResult->wasSuccessful());
    }

    public function testGetWithValidJson(): void
    {
        $testResult = $this->runFixtureTest('testGetWithValidJson');
        self::assertTrue($testResult->wasSuccessful());
    }

    public function testGetWithMissingJsonResponseHeader(): void
    {
        $testResult = $this->runFixtureTest('testGetWithMissingJsonResponseHeader');
        self::assertFalse($testResult->wasSuccessful());

        self::assertSame(1, $testResult->failureCount());

        $exception = $testResult->failures()[0]->thrownException();
        self::assertInstanceOf(ExpectationFailedException::class, $exception);
        self::assertSame('Failed asserting that \'text/html; charset=UTF-8\' matches PCRE pattern "~^application/(ld\+)?json$~iU".', $exception->getMessage());
    }

    public function testGetWithInvalidJson(): void
    {
        $testResult = $this->runFixtureTest('testGetWithInvalidJson');
        self::assertFalse($testResult->wasSuccessful());

        self::assertSame(1, $testResult->failureCount());

        $exception = $testResult->failures()[0]->thrownException();
        self::assertInstanceOf(ExpectationFailedException::class, $exception);
        self::assertSame(<<<ERR
            There was a JSON error of code 4
            Failed asserting that 4 is identical to 0.
            ERR
            , $exception->getMessage()
        );
    }
}
