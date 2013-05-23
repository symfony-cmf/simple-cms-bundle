<?php
namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

class SymfonyCmfSimpleCmsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $container->setParameter($this->getAlias() . '.basepath', $config['basepath']);
        $container->setParameter($this->getAlias() . '.uri_filter_regexp', $config['routing']['uri_filter_regexp']);

        $loader->load('services/routing.xml');
        $loader->load('services/migrator.xml');

        $dynamic = $container->getDefinition($this->getAlias().'.dynamic_router');

        if (!empty($config['generic_controller'])) {
            $definition = new DefinitionDecorator('symfony_cmf_routing.enhancer_explicit_template');
            $definition->replaceArgument(2, $config['generic_controller']);
            $container->setDefinition($this->getAlias() . '.enhancer_explicit_template', $definition);
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_explicit_template')));
        }
        if (!empty($config['routing']['controllers_by_alias'])) {
            $definition = new DefinitionDecorator('symfony_cmf_routing.enhancer_controllers_by_class');
            $definition->replaceArgument(2, $config['routing']['controllers_by_alias']);
            $container->setDefinition($this->getAlias() . '.enhancer_controllers_by_class', $definition);
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_controllers_by_alias')));
        }
        if (!empty($config['routing']['controllers_by_class'])) {
            $definition = new DefinitionDecorator('symfony_cmf_routing.enhancer_controllers_by_class');
            $definition->replaceArgument(2, $config['routing']['controllers_by_class']);
            $container->setDefinition($this->getAlias() . '.enhancer_controllers_by_class', $definition);
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_controllers_by_class')));
        }
        if (!empty($config['generic_controller']) && !empty($config['routing']['templates_by_class'])) {
            $controllerForTemplates = array();
            foreach ($config['routing']['templates_by_class'] as $key => $value) {
                $controllerForTemplates[$key] = $config['generic_controller'];
            }

            $definition = new DefinitionDecorator('symfony_cmf_routing.enhancer_controller_for_templates_by_class');
            $definition->replaceArgument(2, $controllerForTemplates);
            $container->setDefinition($this->getAlias() . '.enhancer_controller_for_templates_by_class', $definition);
            $definition = new DefinitionDecorator('symfony_cmf_routing.enhancer_templates_by_class');
            $definition->replaceArgument(2, $config['routing']['templates_by_class']);
            $container->setDefinition($this->getAlias() . '.enhancer_templates_by_class', $definition);
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_controller_for_templates_by_class')));
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_templates_by_class')));
        }

        $generator = $container->getDefinition($this->getAlias().'.generator');
        $generator->addMethodCall('setContentRepository', array(new Reference($config['routing']['content_repository_id'])));

        $container->setParameter($this->getAlias() . '.manager_name', $config['manager_name']);
        $routeProvider = $container->getDefinition($this->getAlias() . '.route_provider');
        $routeProvider->replaceArgument(0, new Reference($config['manager_registry']));
        $multilangRouteProvider = $container->getDefinition($this->getAlias() . '.multilang_route_provider');
        $multilangRouteProvider->replaceArgument(0, new Reference($config['manager_registry']));

        if (!empty($config['multilang'])) {
            $container->setParameter($this->getAlias() . '.locales', $config['multilang']['locales']);
            $container->setAlias('symfony_cmf_simple_cms.route_provider', 'symfony_cmf_simple_cms.multilang_route_provider');
            if ('Symfony\Cmf\Bundle\SimpleCmsBundle\Document\Page' === $config['document_class']) {
                $config['document_class'] = 'Symfony\Cmf\Bundle\SimpleCmsBundle\Document\MultilangPage';
            }
        }

        $container->setParameter($this->getAlias() . '.document_class', $config['document_class']);

        if ($config['use_menu']) {
            $this->loadMenu($config, $loader, $container);
        }

        if ($config['use_sonata_admin']) {
            $this->loadSonataAdmin($config, $loader, $container);
        } elseif (isset($config['sonata_admin'])) {
            throw new InvalidConfigurationException('Do not define sonata_admin options when use_sonata_admin is set to false');
        }
    }

    protected function loadMenu($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_menu'] && !isset($bundles['SymfonyCmfMenuBundle'])) {
            return;
        }

        $loader->load('services/menu.xml');
    }

    protected function loadSonataAdmin($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_sonata_admin'] && !isset($bundles['SonataDoctrinePHPCRAdminBundle'])) {
            return;
        }

        $container->setParameter($this->getAlias() . '.admin.sort',
            isset($config['sonata_admin'])
                ? $config['sonata_admin']['sort']
                : false
        );

        $loader->load('services/admin.xml');
    }
}
