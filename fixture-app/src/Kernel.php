<?php

namespace App;

use Pierstoval\SmokeTesting\Maker\MakeSmokeTests;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container)
    {
        $container->register(MakeSmokeTests::class)->setAutowired(true)->setAutoconfigured(true);
    }
}
