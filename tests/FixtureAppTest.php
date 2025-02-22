<?php

namespace Pierstoval\SmokeTesting\Tests;

use App\Tests\FunctionalSmokeTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ErrorHandler\ErrorHandler;

require_once __DIR__.'/../fixture-app/tests/bootstrap.php';

class FixtureAppTest extends TestCase
{
    public static function provideRoutes(): array
    {
        return [
            'testGet200' => ['testGet200'],
            'testGet400' => ['testGet400'],
            'testGet500' => ['testGet500'],
            'testGetParameterWithDefault' => ['testGetParameterWithDefault'],
            'testGetWithContentType' => ['testGetWithContentType'],
            'testGetWithEmptyPayload' => ['testGetWithEmptyPayload'],
            'testGetWithPayload' => ['testGetWithPayload'],
            'testGetWithPreRequestCallback' => ['testGetWithPreRequestCallback'],
            'testGetWithValidJson' => ['testGetWithValidJson'],
            'testGetWithValidJsonHeader' => ['testGetWithValidJsonHeader'],
        ];
    }

    /**
     * @dataProvider provideRoutes
     * @runInSeparateProcess
     */
    #[DataProvider('provideRoutes')]
    #[RunInSeparateProcess]
    public function testInternalRoute(string $method): void
    {
        try {
            $result = $this->runFixtureTest($method);
        } catch (\Exception $e) {
            self::fail(\sprintf('EXCEPTION: %s ====== %s', get_class($e), $e->getMessage()));
        }

        self::assertNull($result);
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
}
