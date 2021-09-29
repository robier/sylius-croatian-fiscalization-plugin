<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class RobierSyliusCroatianFiscalizationExtension extends Extension
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');

        $container->setParameter('robier_sylius_croatian_fiscalization_plugin.certificate.production.root_path', $config['certificate']['production']['root_path']);
        $container->setParameter('robier_sylius_croatian_fiscalization_plugin.certificate.production.private_path', $config['certificate']['production']['private_path']);
        $container->setParameter('robier_sylius_croatian_fiscalization_plugin.certificate.production.passphrase', $config['certificate']['production']['passphrase']);

        $container->setParameter('robier_sylius_croatian_fiscalization_plugin.certificate.demo.root_path', $config['certificate']['demo']['root_path']);
        $container->setParameter('robier_sylius_croatian_fiscalization_plugin.certificate.demo.private_path', $config['certificate']['demo']['private_path']);
        $container->setParameter('robier_sylius_croatian_fiscalization_plugin.certificate.demo.passphrase', $config['certificate']['demo']['passphrase']);
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }
}
