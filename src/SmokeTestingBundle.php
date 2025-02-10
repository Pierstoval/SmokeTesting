<?php

namespace Pierstoval\SmokeTesting;

use Pierstoval\SmokeTesting\Command\MakeSmokeTestCommand;
use Pierstoval\SmokeTesting\Maker\MakeSmokeTests;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SmokeTestingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->register(MakeSmokeTests::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;
        $container->register(MakeSmokeTestCommand::class)
            ->setPublic(true)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;
    }
}
