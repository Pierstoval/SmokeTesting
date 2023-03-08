<?php

namespace Pierstoval\SmokeTesting\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class MakeSmokeTestsTest extends KernelTestCase
{
    protected function setup(): void
    {
        (new Filesystem())->remove(self::generatedTestFile());
    }

    /**
     * @dataProvider provideSmokeTestCases
     */
    public function testMakeStateProvider(bool $useDto): void
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('make:smoke-tests'));
        $tester->execute([$useDto ? '--dto' : '--no-dto' => $useDto]);

        $newTestFile = self::generatedTestFile();
        $this->assertFileExists($newTestFile);

        $comparedFile = $useDto
            ? __DIR__ . '/fixtures/FunctionalTestWithDto.php'
            : __DIR__ . '/fixtures/FunctionalTestWithoutDto.php'
        ;

        // Unify line endings
        $expected = preg_replace("~\n +\n~", "\n\n", preg_replace('~\R~u', "\n", file_get_contents($comparedFile)));
        $result = preg_replace("~\n +\n~", "\n\n", preg_replace('~\R~u', "\n", file_get_contents($newTestFile)));
        $this->assertSame($expected, $result);

        $display = $tester->getDisplay();
        $this->assertStringContainsString('Success!', $display);
        $this->assertStringContainsString('Next: Open your new test class and start customizing it.', $display);
    }

    public static function provideSmokeTestCases(): \Generator
    {
        yield 'Generate smoke tests with DTO' => [
            'dto' => true,
        ];

        yield 'Generate smoke tests without DTO' => [
            'dto' => false,
        ];
    }

    private static function generatedTestFile(): string
    {
        return __DIR__.'/../fixture-app/tests/FunctionalTest.php';
    }
}
