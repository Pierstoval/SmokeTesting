<?php

namespace Pierstoval\SmokeTesting\Tests;

use App\Tests\FunctionalSmokeTest;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../fixture-app/tests/bootstrap.php';

class FixtureAppTest extends TestCase
{
    private function runFixtureTest(string $testMethod): ?\Throwable
    {
        $error = null;
        try {
            $test = new FunctionalSmokeTest($testMethod);
            $test->{$testMethod}();
        } catch (\Throwable $e) {
            $error = $e;
        }

        return $error;
    }

    public function testGet200(): void
    {
        self::assertNull($this->runFixtureTest('testGet200'));
    }

    public function testGet400(): void
    {
        self::assertNull($this->runFixtureTest('testGet400'));
    }

    public function testGet500(): void
    {
        self::assertNull($this->runFixtureTest('testGet500'));
    }

    public function testGetParameterWithoutDefault(): void
    {
        self::assertNull($this->runFixtureTest('testGetParameterWithoutDefault'));
    }

    public function testGetParameterWithDefault(): void
    {
        self::assertNull($this->runFixtureTest('testGetParameterWithDefault'));
    }

    public function testGetWithEmptyPayload(): void
    {
        self::assertNull($this->runFixtureTest('testGetWithEmptyPayload'));
    }

    public function testGetWithPayload(): void
    {
        self::assertNull($this->runFixtureTest('testGetWithPayload'));
    }

    public function testGetWithValidJson(): void
    {
        self::assertNull($this->runFixtureTest('testGetWithValidJson'));
    }

    public function testGetWithValidJsonHeader(): void
    {
        self::assertNull($this->runFixtureTest('testGetWithValidJsonHeader'));
    }

    public function testGetWithPreRequestCallback(): void
    {
        self::assertNull($this->runFixtureTest('testGetWithPreRequestCallback'));
    }

    public function testGetWithContentType(): void
    {
        self::assertNull($this->runFixtureTest('testGetWithContentType'));
    }

    public function testGetWithMissingJsonResponseHeader(): void
    {
        self::assertNotNull($exception = $this->runFixtureTest('testGetWithMissingJsonResponseHeader'));

        self::assertInstanceOf(ExpectationFailedException::class, $exception);
        self::assertSame('Failed asserting that \'text/html; charset=UTF-8\' matches PCRE pattern "~application/(ld\+)?json~iU".', $exception->getMessage());
    }

    public function testGetWithInvalidJson(): void
    {
        self::assertNotNull($exception = $this->runFixtureTest('testGetWithInvalidJson'));

        self::assertInstanceOf(ExpectationFailedException::class, $exception);
        self::assertSame(<<<ERR
            There was a JSON error of code 4
            Failed asserting that 4 is identical to 0.
            ERR
            , $exception->getMessage()
        );
    }
}
