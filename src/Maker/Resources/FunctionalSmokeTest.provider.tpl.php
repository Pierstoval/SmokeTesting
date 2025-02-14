<?php declare(strict_types=1);
echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use PHPUnit\Framework\Attributes\DataProvider;
<?php if ($with_dto): ?>
use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
<?php endif; ?>
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class <?php echo $class_name; ?> extends WebTestCase
{
<?php if ($with_dto): ?>
    use FunctionalSmokeTester;
<?php endif; ?>

    public static function provideRoutes(): \Generator
    {
    <?php foreach ($routes as $route): ?>
        <?php if (str_starts_with($route['routePath'], '/_')) continue;
        ?> yield '<?php echo $route['httpMethod']; ?> <?php echo $route['routePath']; ?>' => ['<?php echo $route['httpMethod']; ?>', '<?php echo $route['routePath']; ?>','<?php echo $route['routeName']; ?>'];
    <?php endforeach; ?>
    }

    #[DataProvider('provideRoutes')]
    public function testRoute(string $method, string $url, string $route): void
    {
<?php if ($with_dto): ?>
        $this->runFunctionalTest(
            FunctionalTestData::withUrl($url)
                ->withMethod($method)
                ->expectRouteName($route)
                ->appendCallableExpectation($this->assertStatusCodeLessThan500($method, $url))
        );
<?php else: ?>
    $client = static::createClient();
    $client->request($method, $url);

    static::assertLessThan(
    500,
    $client->getResponse()->getStatusCode(),
    \sprintf(
        'Request "%s %s" for route "%s" returned an internal error.',
        $method, $url, $route
    ),
    );
<?php endif; ?>

    }

<?php if ($with_dto): ?>
    public function assertStatusCodeLessThan500(string $method, string $url): \Closure
    {
        return static function (KernelBrowser $browser) use ($method, $url) {
            $statusCode = $browser->getResponse()->getStatusCode();
            $routeName = $browser->getRequest()->attributes->get('_route', 'unknown');

            static::assertLessThan(
                500,
                $statusCode,
                sprintf('Request "%s %s" for %s route returned an internal error.', $method, $url, $routeName),
            );
        };
    }
<?php endif; ?>}
