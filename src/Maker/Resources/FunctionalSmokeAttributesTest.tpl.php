<?php declare(strict_types=1);
echo "<?php\n"; ?>

namespace <?php echo $namespace; ?>;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\Attributes\TestDox;
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
<?php foreach ($routes as $route): ?>
    <?php if (!str_starts_with($route['routePath'], '/_')): ?>
    #[TestWith(['<?php echo $route['routePath']; ?>','<?php echo $route['routeName']; ?>', '<?php echo $route['httpMethod']; ?>'])]
    <?php endif; ?>
    <?php endforeach; ?>
    #[TestDox('/$method $url ($route)')]
    public function testRoute(string $url, string $route, string $method='GET'): void
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
    $crawler = $client->request('GET', '/');

    static::assertLessThan(
    500,
    $client->getResponse()->getStatusCode(),
    'Request "$method $url for route $routeName returned an internal error.',
    );
<?php endif; ?>

    }

<?php if ($with_dto): ?>
    public function assertStatusCodeLessThan500(string $method, string $url): \Closure
    {
        return function (KernelBrowser $browser) use ($method, $url) {
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
