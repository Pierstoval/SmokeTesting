<?php declare(strict_types=1);
echo "<?php\n"; ?>

namespace <?php echo $namespace ?? 'UndefinedNamespace'; ?>;

<?php if ($with_dto ?? false): ?>
use Pierstoval\SmokeTesting\FunctionalSmokeTester;
use Pierstoval\SmokeTesting\FunctionalTestData;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
<?php endif; ?>
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class <?php echo $class_name ?? 'UndefinedClassName' ?> extends WebTestCase
{
<?php if ($with_dto ?? false): ?>
    use FunctionalSmokeTester;
<?php endif; ?>
<?php foreach ($routes ?? [] as $route): ?>

    public function testRoute<?php echo ucfirst(preg_replace_callback('~_([a-z0-9])~isUu', function($matches) {return strtoupper($matches[1]);}, $route['routeName'])).'WithMethod'.ucfirst(strtolower($route['httpMethod'])); ?>(): void
    {
<?php if ($with_dto ?? false): ?>
        $this->runFunctionalTest(
            FunctionalTestData::withUrl('<?php echo $route['routePath']; ?>')
                ->withMethod('<?php echo $route['httpMethod']; ?>')
                ->expectRouteName('<?php echo $route['routeName']; ?>')
                ->appendCallableExpectation($this->assertStatusCodeLessThan500('<?php echo $route['httpMethod']; ?>', '<?php echo $route['routePath']; ?>'))
        );
<?php else: ?>
        $client = static::createClient();
        $client->request('GET', '/');

        static::assertLessThan(
            500,
            $client->getResponse()->getStatusCode(),
            'Request "<?php echo $route['httpMethod'].' '.$route['routePath']; ?>" for route "<?php echo $route['routeName']; ?>" returned an internal error.',
        );
<?php endif; ?>
    }
    <?php endforeach; ?>

<?php if ($with_dto ?? false): ?>
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
