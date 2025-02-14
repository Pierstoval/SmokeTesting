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
    public function testMakeStateProvider(bool $useDto, bool $useAttributes = false, bool $useProvider = false): void
    {
        $tester = new CommandTester((new Application(self::bootKernel()))->find('make:smoke-tests'));
        $params = [
            $useDto ? '--dto' : '--no-dto' => $useDto,
        ];
        if ($useAttributes) {
            $params['--use-attributes'] = true;
        }
        if ($useProvider) {
            $params['--use-provider'] = true;
        }
        $tester->execute($params);

        $newTestFile = self::generatedTestFile();
        $this->assertFileExists($newTestFile);

        $comparedFile = match (true) {
            $useDto && $useAttributes => __DIR__ . '/fixtures/FunctionalTestWithDto.attributes.php',
            $useDto && $useProvider => __DIR__ . '/fixtures/FunctionalTestWithDto.provider.php',
            !$useDto && $useAttributes => __DIR__ . '/fixtures/FunctionalTestWithoutDto.attributes.php',
            !$useDto && $useProvider => __DIR__ . '/fixtures/FunctionalTestWithoutDto.provider.php',
            $useDto && !$useAttributes && !$useProvider => __DIR__ . '/fixtures/FunctionalTestWithDto.php',
            !$useDto && !$useAttributes && !$useProvider => __DIR__ . '/fixtures/FunctionalTestWithoutDto.php',
        };

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
        yield 'Generate smoke tests with DTO' => ['useDto' => true];
        yield 'Generate smoke tests without DTO' => ['useDto' => false];
        yield 'Generate smoke tests with DTO and TestWith attributes' => ['useDto' => true, 'useAttributes' => true];
        yield 'Generate smoke tests without DTO and TestWith attributes' => ['useDto' => false, 'useAttributes' => true];
        yield 'Generate smoke tests with DTO and DatProvider' => ['useDto' => true, 'useProvider' => true];
        yield 'Generate smoke tests without DTO and DatProvider' => ['useDto' => false, 'useProvider' => true];
    }

    private static function generatedTestFile(): string
    {
        return __DIR__.'/../fixture-app/tests/FunctionalTest.php';
    }
}
