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
        $container->setParameter($this->getAlias() . '.document_class', $config['document_class']);

        $container->setParameter($this->getAlias() . '.generic_controller', $config['generic_controller']);
        $container->setParameter($this->getAlias() . '.controllers_by_class', $config['routing']['controllers_by_class']);
        $container->setParameter($this->getAlias() . '.templates_by_class', $config['routing']['templates_by_class']);

        $loader->load('services/routing.xml');

        $doctrine = $container->getDefinition($this->getAlias().'.doctrine_router');

        // if any mappings are defined, set the respective mappers
        if (!empty($config['routing']['controllers_by_class'])) {
            $doctrine->addMethodCall('addControllerMapper', array(new Reference($this->getAlias() . '.mapper_controllers_by_class')));
        }
        if (!empty($config['generic_controller']) && !empty($config['routing']['templates_by_class'])) {
            $doctrine->addMethodCall('addControllerMapper', array(new Reference($this->getAlias() . '.mapper_templates_by_class')));
        }


        $loader->load('services/menu.xml');

        // TODO: make admin optional
        $loader->load('services/admin.xml');
    }
}
