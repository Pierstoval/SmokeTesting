<?php

namespace Pierstoval\SmokeTesting\Maker;

use Pierstoval\SmokeTesting\FunctionalTestData;
use Pierstoval\SmokeTesting\RoutesExtractor;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Routing\RouterInterface;

class MakeSmokeTests extends AbstractMaker
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getCommandName(): string
    {
        return 'make:smoke-tests';
    }

    public static function getCommandDescription(): string
    {
        return 'Create functional smoke tests in one single class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command->addOption('dto', null, InputOption::VALUE_NEGATABLE, \sprintf('Enables (or disable --no-dto) tests using the "%s" DTO class.', \basename(FunctionalTestData::class)), true);
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        if (!$this->router->getRouteCollection()->count()) {
            throw new \RuntimeException('No routes found in the application.');
        }

        $stateProviderClassNameDetails = $generator->createClassNameDetails('Functional', 'Tests', 'Test');

        $generator->generateClass($stateProviderClassNameDetails->getFullName(), __DIR__.'/Resources/FunctionalSmokeTest.tpl.php', [
            'routes' => RoutesExtractor::extractRoutesFromRouter($this->router),
            'with_dto' => $input->getOption('dto'),
        ]);
        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text([
            'Next: Open your new test class and start customizing it.',
        ]);
    }
}
