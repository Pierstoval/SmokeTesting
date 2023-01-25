<?php

namespace Pierstoval\SmokeTesting;

use Pierstoval\SmokeTesting\Maker\MakeSmokeTests;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SmokeTestingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->register(MakeSmokeTests::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;
    }
}
