<?php

namespace Pierstoval\SmokeTesting\Command;

use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\Attributes\TestWith;
use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Pierstoval\SmokeTesting\RoutesExtractor;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand('generate:smoke', 'Generate the smoke test')]
final class MakeSmokeTestCommand extends Command
{

    public function __construct(
        private readonly RouterInterface                          $router,
        #[Autowire('%kernel.project_dir%/tests/')] private string $testDir,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('className', InputArgument::OPTIONAL, 'Test class name', 'StaticRoutesTest')
            ->addOption('dto', null, InputOption::VALUE_NONE, 'Create with DTO')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite existing files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dto = $input->getOption('dto');
        $dto = true;
        $force = true;
        $routes = RoutesExtractor::extractRoutesFromRouter($this->router);
        if (!class_exists(PhpNamespace::class)) {
            $output->writeln("Missing dependency:\n\ncomposer req nette/php-generator");
            return self::FAILURE;
        }
        $namespace = new PhpNamespace('Tests');
        foreach ([
                     FunctionalSmokeTester::class,
                     WebTestCase::class,
                     TestDox::class,
                     TestWith::class,
                     KernelBrowser::class,
                     FunctionalTestData::class
                 ] as $useClass) {
            $namespace->addUse($useClass);
        }


// create new classes in the namespace
        $className = $input->getArgument('className');
        $class = $namespace->addClass($className);
        $class->setExtends(WebTestCase::class);
        $namespace->add($class);

        $class->addTrait(FunctionalSmokeTester::class);

        $method = $class->addMethod('testRoute');
        $method->setReturnType('void');
//        #[TestDox('/$method $url ($route)')]
        $method->addAttribute(TestDox::class, [
            '/$method $url ($route)'
        ]);
        // get the routes
        foreach ($routes as $route) {
            // these patterns could also be defined in the bundle
            foreach (['_wdt_','_profiler_'] as $prefix) {
                if (str_starts_with($route['routeName'], $prefix)) {
                    continue 2;
                }
            }
            $method->addAttribute(TestWith::class,
                [
                    [
                        $route['httpMethod'],
                        $route['routePath'],
                        $route['routeName']
                    ]
                ]);
        }

        array_map(fn($param) => $method->addParameter($param)->setType('string'), [
            'method', 'url', 'route'
        ]);
//        public function testRoute(string $method, string $url, string $route): void
        if ($dto) {
            $method->setBody(<<<'END'
        $this->runFunctionalTest(
            FunctionalTestData::withUrl($url)
                ->withMethod($method)
                ->expectRouteName($route)
                ->appendCallableExpectation($this->assertStatusCodeLessThan500($method, $url))
        );
END
            );

            $method = $class->addMethod('assertStatusCodeLessThan500');
            array_map(fn($param) => $method->addParameter($param)->setType('string'), [
                'method', 'url'
            ]);
            $method->setReturnType('\Closure');
            $method->setBody(<<<'END'
                return function (KernelBrowser $browser) use ($method, $url) {
                    $statusCode = $browser->getResponse()->getStatusCode();
                    $routeName = $browser->getRequest()->attributes->get('_route', 'unknown');

                    static::assertLessThan(
                        500,
                        $statusCode,
                        sprintf('Request "%s %s" for %s route returned an internal error.', $method, $url, $routeName),
                    );
                };
    END
            );

        } else {
            $method->setBody(<<<'END'
    $client = static::createClient();
    $crawler = $client->request('GET', '/');

    static::assertLessThan(
    500,
    $client->getResponse()->getStatusCode(),
    'Request "$method $url for route $routeName returned an internal error.',
    );
END
            );
        }
        $filename = $this->testDir . $input->getArgument('className') . '.php';
        if ($input->getOption('force') || !file_exists($filename)) {
            file_put_contents($filename, "<?php\n\n" . $namespace);
        }
        $output->writeln(sprintf('<info>%s</info> written.', $filename));

        return self::SUCCESS;

    }


}
