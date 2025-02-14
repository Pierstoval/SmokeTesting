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
        $command->addOption('dto', 'd', InputOption::VALUE_NEGATABLE, \sprintf('Enables (or disable --no-dto) tests using the "%s" DTO class.', \basename(\str_replace('\\', '/', FunctionalTestData::class))), true);
        $command->addOption('use-attributes', 'a', InputOption::VALUE_NONE, 'If enabled, uses in #[TestWith] attribute to provide routes.');
        $command->addOption('use-provider', 'p', InputOption::VALUE_NONE, 'If enabled, uses in #[DataProvider] attribute to provide routes.');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        parent::interact($input, $io, $command);

        if ($input->getOption('use-attributes') && $input->getOption('use-provider')) {
            throw new \RuntimeException(\sprintf(
                'Cannot set both "%s" and "%s" attribute at the same time. Use only one of them.',
                    'use-attributes', 'use-provider'
            ));
        }
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        if (!$this->router->getRouteCollection()->count()) {
            throw new \RuntimeException('No routes found in the application.');
        }

        $stateProviderClassNameDetails = $generator->createClassNameDetails('Functional', 'Tests', 'Test');

        if ($input->getOption('use-attributes')) {
            $template = __DIR__ . '/Resources/FunctionalSmokeTest.attributes.tpl.php';
        } elseif ($input->getOption('use-provider')) {
            $template = __DIR__ . '/Resources/FunctionalSmokeTest.provider.tpl.php';
        } else {
            $template = __DIR__ . '/Resources/FunctionalSmokeTest.tpl.php';
        }

        $generator->generateClass($stateProviderClassNameDetails->getFullName(), $template, [
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
