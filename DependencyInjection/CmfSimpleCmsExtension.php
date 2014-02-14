<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

use PHPCR\Util\PathHelper;

class CmfSimpleCmsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $prependConfig = array('persistence' => array('phpcr' => (array('enabled' => true))));
        $container->prependExtensionConfig('cmf_menu', $prependConfig);
        $prependConfig = array('dynamic' => $prependConfig);
        $container->prependExtensionConfig('cmf_routing', $prependConfig);
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if ($config['routing']) {
            $this->loadRouting($config['routing'], $loader, $container);
        }

        if ($config['persistence']['phpcr']) {
            $this->loadPhpcr($config['persistence']['phpcr'], $loader, $container);

            if ($config['use_menu']) {
                $this->loadPhpcrMenu($config, $loader, $container);
            }
        }
    }

    protected function loadRouting($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['uri_filter_regexp'])) {
            throw new InvalidConfigurationException('cmf_simple.routing.uri_filter_regexp must be configured on cmf_routing.');
        }

        $loader->load('routing-bc.xml');

        if (!empty($config['generic_controller'])) {
            $container->setParameter($this->getAlias() . '.generic_controller', $config['generic_controller']);
            $definition = $container->getDefinition($this->getAlias() . '.enhancer.explicit_template');
            $definition->addTag('dynamic_router_route_enhancer', array('priority' => -1000));
        } else {
            $container->removeDefinition($this->getAlias() . '.enhancer.explicit_template');
        }

        if (!empty($config['controllers_by_type'])) {
            $container->setParameter($this->getAlias() . '.controllers_by_type', $config['controllers_by_type']);
            $definition = $container->getDefinition($this->getAlias() . '.enhancer.controllers_by_type');
            $definition->addTag('dynamic_router_route_enhancer', array('priority' => -1000));
        } else {
            $container->removeDefinition($this->getAlias() . '.enhancer.controllers_by_type');
        }

        if (!empty($config['controllers_by_class'])) {
            $container->setParameter($this->getAlias() . '.controllers_by_class', $config['controllers_by_class']);
            $definition = $container->getDefinition($this->getAlias() . '.enhancer.controllers_by_class');
            $definition->addTag('dynamic_router_route_enhancer', array('priority' => -1000));
        } else {
            $container->removeDefinition($this->getAlias() . '.enhancer.controllers_by_class');
        }

        if (!empty($config['generic_controller']) && !empty($config['templates_by_class'])) {
            $controllerForTemplates = array();
            foreach ($config['templates_by_class'] as $key => $value) {
                $controllerForTemplates[$key] = $config['generic_controller'];
            }
            $definition = $container->getDefinition($this->getAlias() . '.enhancer.controller_for_templates_by_class');
            $definition->replaceArgument(2, $controllerForTemplates);
            $definition->addTag('dynamic_router_route_enhancer', array('priority' => -1000));

            $container->setParameter($this->getAlias() . '.templates_by_class', $config['templates_by_class']);
            $definition = $container->getDefinition($this->getAlias() . '.enhancer.templates_by_class');
            $definition->addTag('dynamic_router_route_enhancer', array('priority' => -1000));
        } else {
            $container->removeDefinition($this->getAlias() . '.enhancer.controller_for_templates_by_class');
            $container->removeDefinition($this->getAlias() . '.enhancer.templates_by_class');
        }
    }

    protected function loadPhpcr($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $loader->load('services-phpcr.xml');
        $loader->load('migrator-phpcr.xml');

        $prefix = $this->getAlias() . '.persistence.phpcr';

        $container->setParameter($prefix . '.basepath', $config['basepath']);

        $container->setParameter($prefix . '.menu_basepath', PathHelper::getParentPath($config['basepath']));

        if ($config['use_sonata_admin']) {
            $this->loadSonataAdmin($config, $loader, $container);
        } elseif (isset($config['sonata_admin'])) {
            throw new InvalidConfigurationException('Do not define sonata_admin options when use_sonata_admin is set to false');
        }

        $container->setParameter($prefix . '.manager_name', $config['manager_name']);

        $container->setParameter($prefix . '.document.class', $config['document_class']);
    }

    protected function loadPhpcrMenu($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_menu'] && !isset($bundles['CmfMenuBundle'])) {
            return;
        }

        $loader->load('menu-phpcr.xml');
    }

    protected function loadSonataAdmin($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_sonata_admin'] && !isset($bundles['SonataDoctrinePHPCRAdminBundle'])) {
            return;
        }

        $container->setParameter($this->getAlias() . '.persistence.phpcr.admin.sort',
            isset($config['sonata_admin'])
            ? $config['sonata_admin']['sort']
            : false
        );

        $loader->load('admin-phpcr.xml');
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://cmf.symfony.com/schema/dic/simplecms';
    }
}
