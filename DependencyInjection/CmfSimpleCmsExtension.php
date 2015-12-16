<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
        // process the configuration of CmfCoreExtension
        $configs = $container->getExtensionConfig($this->getAlias());
        $parameterBag = $container->getParameterBag();
        $configs = $parameterBag->resolveValue($configs);
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (empty($config['persistence']['phpcr']['enabled'])) {
            return;
        }

        $prependConfig = array(
            'chain' => array(
                'routers_by_id' => array(
                    'router.default' => 0,
                    'cmf_routing.dynamic_router' => -100,
                ),
            ),
            'dynamic' => array(
                'enabled' => true,
            ),
        );

        $container->prependExtensionConfig('cmf_routing', $prependConfig);
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if ($config['persistence']['phpcr']) {
            $this->loadPhpcr($config['persistence']['phpcr'], $loader, $container);

            if ($config['use_menu']) {
                $this->loadPhpcrMenu($config, $loader, $container);
            }
        }

        $this->loadIvoryCKEditor($config['ivory_ckeditor'], $container);
    }

    protected function loadIvoryCKEditor(array $config, ContainerBuilder $container)
    {
        $container->setParameter($this->getAlias().'.ivory_ckeditor.config', array());

        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['enabled'] && !isset($bundles['IvoryCKEditorBundle'])) {
            return;
        }

        if (true === $config['enabled'] && !isset($bundles['IvoryCKEditorBundle'])) {
            $message = 'IvoryCKEditorBundle integration was explicitely enabled, but the bundle is not available';

            if (class_exists('Ivory\CKEditorBundle\IvoryCKEditorBundle')) {
                $message .= ' (did you forget to register the bundle in the AppKernel?)';
            }

            throw new \LogicException($message.'.');
        }

        if (false === $config['enabled'] || !isset($bundles['IvoryCKEditorBundle'])) {
            return;
        }

        $container->setParameter($this->getAlias().'.ivory_ckeditor.config', array(
            'config_name' => $config['config_name'],
        ));
    }

    protected function loadPhpcr($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $loader->load('services-phpcr.xml');
        $loader->load('migrator-phpcr.xml');

        $prefix = $this->getAlias().'.persistence.phpcr';

        $container->setParameter($prefix.'.basepath', $config['basepath']);

        $container->setParameter($prefix.'.menu_basepath', PathHelper::getParentPath($config['basepath']));

        if ($config['use_sonata_admin']) {
            $this->loadSonataAdmin($config, $loader, $container);
        } elseif (isset($config['sonata_admin'])) {
            throw new InvalidConfigurationException('Do not define sonata_admin options when use_sonata_admin is set to false');
        }

        $container->setParameter($prefix.'.manager_name', $config['manager_name']);

        $container->setParameter($prefix.'.document.class', $config['document_class']);
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

        $container->setParameter($this->getAlias().'.persistence.phpcr.admin.sort',
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
