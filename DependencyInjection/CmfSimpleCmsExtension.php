<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
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

        $locales = false;
        if (isset($config['multilang']['locales'])) {
            $locales = $config['multilang']['locales'];
            $container->setParameter($this->getAlias() . '.multilang.locales', $locales);
        }
        $this->loadRouting($config['routing'], $loader, $container);

        if ($config['persistence']['phpcr']) {
            $this->loadPhpcr($config['persistence']['phpcr'], $loader, $container);
            $this->loadPhpcrRouting($config, $loader, $container, $locales);

            if ($config['use_menu']) {
                $this->loadPhpcrMenu($config, $loader, $container);
            }
        }
    }

    protected function loadRouting($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $container->setParameter($this->getAlias() . '.uri_filter_regexp', $config['uri_filter_regexp']);

        $loader->load('routing.xml');

        $dynamic = $container->getDefinition($this->getAlias().'.dynamic_router');

        if (!empty($config['generic_controller'])) {
            $definition = new DefinitionDecorator('cmf_routing.enhancer.explicit_template');
            $definition->replaceArgument(2, $config['generic_controller']);
            $container->setDefinition(
                $this->getAlias() . '.enhancer.explicit_template',
                $definition
            );
            $dynamic->addMethodCall('addRouteEnhancer', array(
                new Reference($this->getAlias() . '.enhancer.explicit_template')
            ));
        }

        if (!empty($config['controllers_by_alias'])) {
            $definition = new DefinitionDecorator('cmf_routing.enhancer.controllers_by_class');
            $definition->replaceArgument(2, $config['routing']['controllers_by_alias']);
            $container->setDefinition(
                $this->getAlias() . '.enhancer.controllers_by_class',
                $definition
            );
            $dynamic->addMethodCall('addRouteEnhancer', array(
                new Reference($this->getAlias() . '.enhancer.controllers_by_alias')
            ));
        }

        if (!empty($config['controllers_by_class'])) {
            $definition = new DefinitionDecorator('cmf_routing.enhancer.controllers_by_class');
            $definition->replaceArgument(2, $config['controllers_by_class']);
            $container->setDefinition(
                $this->getAlias() . '.enhancer.controllers_by_class',
                $definition
            );
            $dynamic->addMethodCall('addRouteEnhancer', array(
                new Reference($this->getAlias() . '.enhancer.controllers_by_class')
            ));
        }

        if (!empty($config['generic_controller']) && !empty($config['templates_by_class'])) {
            $controllerForTemplates = array();
            foreach ($config['templates_by_class'] as $key => $value) {
                $controllerForTemplates[$key] = $config['generic_controller'];
            }

            $definition = new DefinitionDecorator('cmf_routing.enhancer.controller_for_templates_by_class');
            $definition->replaceArgument(2, $controllerForTemplates);

            $container->setDefinition(
                $this->getAlias() . '.enhancer.controller_for_templates_by_class',
                $definition
            );

            $definition = new DefinitionDecorator('cmf_routing.enhancer.templates_by_class');
            $definition->replaceArgument(2, $config['templates_by_class']);

            $container->setDefinition(
                $this->getAlias() . '.enhancer.templates_by_class',
                $definition
            );

            $dynamic->addMethodCall('addRouteEnhancer', array(
                new Reference($this->getAlias() . '.enhancer.controller_for_templates_by_class')
            ));
            $dynamic->addMethodCall('addRouteEnhancer', array(
                new Reference($this->getAlias() . '.enhancer.templates_by_class')
            ));
        }
    }

    protected function loadPhpcr($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        // migrator is only for PHPCR
        $loader->load('migrator.xml');

        // save some characters
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

    protected function loadPhpcrRouting($config, XmlFileLoader $loader, ContainerBuilder $container, $locales)
    {
        $loader->load('routing-phpcr.xml');
        $prefix = $this->getAlias() . '.persistence.phpcr';

        $routeProvider = $container->getDefinition($prefix.'.route_provider');
        $routeProvider->replaceArgument(0, new Reference($config['persistence']['phpcr']['manager_registry']));
        if (!empty($locales)) {
            $routeProvider->addMethodCall('setLocales', array($locales));
        }
        $container->setAlias($this->getAlias() . '.route_provider', $prefix.'.route_provider');

        $generator = $container->getDefinition($this->getAlias().'.generator');
        $generator->addMethodCall('setContentRepository', array(
            new Reference($config['routing']['content_repository_id'])
        ));
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

        $loader->load('admin.xml');
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
