<?php

declare(strict_types=1);

namespace Robier\SyliusCroatianFiscalizationPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('robier_sylius_croatian_fiscalization');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('disable_on_payment_codes')->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
                ->enumNode('environment')->values(['demo', 'production'])->end()
                ->arrayNode('resending')
                    ->children()
                        ->integerNode('max_attempts')->defaultValue(3)->end()
                    ->end()
                ->end() // resending
                ->arrayNode('certificate')
                    ->children()
                        ->arrayNode('demo')
                            ->children()
                                ->scalarNode('root_path')->cannotBeEmpty()->end()
                                ->scalarNode('private_path')->cannotBeEmpty()->end()
                                ->scalarNode('passphrase')->cannotBeEmpty()->end()
                            ->end()
                        ->end() // demo
                        ->arrayNode('production')
                            ->children()
                                ->scalarNode('root_path')->cannotBeEmpty()->end()
                                ->scalarNode('private_path')->cannotBeEmpty()->end()
                                ->scalarNode('passphrase')->cannotBeEmpty()->end()
                            ->end()
                        ->end() // production
                    ->end()
                ->end() // certificate
                ->arrayNode('company')
                    ->children()
                        ->scalarNode('oib')->cannotBeEmpty()->end()
                        ->booleanNode('inside_tax_registry')->defaultTrue()->end()
                    ->end()
                ->end() // company
                ->arrayNode('operator')
                    ->children()
                        ->scalarNode('oib')->defaultNull()->end()
                    ->end()
                ->end() // operator
            ->end();


        return $treeBuilder;
    }
}
