<?php

namespace Pierstoval\SmokeTesting\Tests\Mock;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class MockKernel implements KernelInterface
{
    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        throw new \LogicException(__METHOD__.'() should not be called.');
    }

    public function registerBundles(): iterable
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }

    public function boot(): void
    {
    }

    public function shutdown(): void
    {
    }

    public function getBundles(): array
    {
        return [];
    }

    public function getBundle(string $name): BundleInterface
    {
        throw new \LogicException(__METHOD__.' should not be called.');
    }

    public function locateResource(string $name): string
    {
        throw new \LogicException(__METHOD__.' should not be called.');
    }

    public function getEnvironment(): string
    {
        return 'test';
    }

    public function isDebug(): bool
    {
        return false;
    }

    public function getProjectDir(): string
    {
        return sys_get_temp_dir();
    }

    public function getContainer(): ContainerInterface
    {
        throw new \LogicException(__METHOD__.' should not be called.');
    }

    public function getStartTime(): float
    {
        return 0.0;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir();
    }

    public function getBuildDir(): string
    {
        return sys_get_temp_dir();
    }

    public function getShareDir(): ?string
    {
        return null;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir();
    }

    public function getCharset(): string
    {
        return 'UTF-8';
    }
}
