<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder->root('symfony_cmf_simple_cms')
            ->children()
                ->enumNode('use_sonata_admin')
                    ->values(array(true, false, 'auto'))
                    ->defaultValue('auto')
                ->end()
                ->enumNode('use_menu')
                    ->values(array(true, false, 'auto'))
                    ->defaultValue('auto')
                ->end()
                ->scalarNode('document_class')->defaultValue('Symfony\Cmf\Bundle\SimpleCmsBundle\Document\Page')->end()
                ->scalarNode('generic_controller')->defaultValue('symfony_cmf_content.controller:indexAction')->end()
                ->scalarNode('basepath')->defaultValue('/cms/simple')->end()
                ->arrayNode('routing')
                    ->children()
                        ->scalarNode('content_repository_id')->defaultValue('symfony_cmf_routing_extra.content_repository')->end()
                        ->scalarNode('uri_filter_regexp')->defaultValue('')->end()
                        ->arrayNode('controllers_by_class')
                            ->useAttributeAsKey('alias')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('templates_by_class')
                            ->useAttributeAsKey('alias')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('multilang')
                    ->children()
                        ->arrayNode('locales')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
