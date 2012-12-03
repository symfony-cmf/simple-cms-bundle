<?php
namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
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

        $container->setParameter($this->getAlias() . '.generic_controller', $config['generic_controller']);
        $container->setParameter($this->getAlias() . '.controllers_by_class', $config['routing']['controllers_by_class']);
        $container->setParameter($this->getAlias() . '.templates_by_class', $config['routing']['templates_by_class']);
        $container->setParameter($this->getAlias() . '.uri_filter_regexp', $config['routing']['uri_filter_regexp']);

        $loader->load('services/routing.xml');

        $dynamic = $container->getDefinition($this->getAlias().'.dynamic_router');

        // if any mappings are defined, set the respective mappers
        if (!empty($config['routing']['controllers_by_class'])) {
            $dynamic->addMethodCall('addControllerMapper', array(new Reference($this->getAlias() . '.mapper_controllers_by_class')));
        }
        if (!empty($config['generic_controller']) && !empty($config['routing']['templates_by_class'])) {
            $dynamic->addMethodCall('addControllerMapper', array(new Reference($this->getAlias() . '.mapper_templates_by_class')));
        }

        $dynamic->addMethodCall('setContentRepository', array(new Reference($config['routing']['content_repository_id'])));

        if (!empty($config['multilang'])) {
            $container->setParameter($this->getAlias() . '.locales', $config['multilang']['locales']);
            $container->setAlias('symfony_cmf_simple_cms.route_repository', 'symfony_cmf_simple_cms.multilang_route_repository');
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

        $loader->load('services/admin.xml');
    }
}
