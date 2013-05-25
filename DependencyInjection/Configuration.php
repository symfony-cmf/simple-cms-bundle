<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder->root('cmf_simple_cms')
            ->children()
                ->enumNode('use_sonata_admin')
                    ->values(array(true, false, 'auto'))
                    ->defaultValue('auto')
                ->end()
                ->arrayNode('sonata_admin')
                    ->children()
                        ->enumNode('sort')
                            ->values(array(false, 'asc', 'desc'))
                            ->defaultValue(false)
                        ->end()
                    ->end()
                ->end()
                ->enumNode('use_menu')
                    ->values(array(true, false, 'auto'))
                    ->defaultValue('auto')
                ->end()
                ->scalarNode('document_class')->defaultValue('Symfony\Cmf\Bundle\SimpleCmsBundle\Document\Page')->end()
                ->scalarNode('generic_controller')->defaultValue('cmf_content.controller:indexAction')->end()
                ->scalarNode('basepath')->defaultValue('/cms/simple')->end()
                ->scalarNode('manager_registry')->defaultValue('doctrine_phpcr')->end()
                ->scalarNode('manager_name')->defaultValue('default')->end()
                ->arrayNode('routing')
                    ->children()
                        ->scalarNode('content_repository_id')->defaultValue('cmf_routing.content_repository')->end()
                        ->scalarNode('uri_filter_regexp')->defaultValue('')->end()
                        ->arrayNode('controllers_by_alias')
                            ->useAttributeAsKey('alias')
                            ->prototype('scalar')->end()
                        ->end()
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
