<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('symfony_cmf_simple_cms');

        $rootNode
            ->children()
                ->scalarNode('use_sonata_admin')->defaultTrue()->end()
                ->scalarNode('document_class')->defaultValue('Symfony\Cmf\Bundle\SimpleCmsBundle\Document\Page')->end()
                ->scalarNode('generic_controller')->defaultValue('symfony_cmf_content.controller:indexAction')->end()
                ->scalarNode('basepath')->defaultValue('/cms/simple')->end()
                ->arrayNode('routing')
                    ->children()
                        ->scalarNode('content_repository_id')->defaultValue('symfony_cmf_routing_extra.content_repository')->end()
                        ->arrayNode('controllers_by_class')
                            ->useAttributeAsKey('alias')
                            ->prototype('scalar')
                            /* why does this not work?
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('Symfony\Cmf\Component\Routing\RedirectRouteInterface')
                                        ->defaultValue('symfony_cmf_routing_extra.redirect_controller:redirectAction')
                                    ->end()
                                ->end()
                             */
                        ->end()
                    ->end()
                    ->arrayNode('templates_by_class')
                        ->useAttributeAsKey('alias')
                        ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('multilang')
                    ->children()
                        ->arrayNode('locales')
                            ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
