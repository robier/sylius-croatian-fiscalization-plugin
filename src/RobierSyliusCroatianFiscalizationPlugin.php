<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class RobierSyliusCroatianFiscalizationPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container)
    {
        $paths = [
            realpath(__DIR__ . '/Resources/config/doctrine') => 'Robier\SyliusCroatianFiscalizationPlugin\Entity',
        ];
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createXmlMappingDriver($paths)
        );
    }
}
