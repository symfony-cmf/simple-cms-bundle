<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$container->loadFromExtension('cmf_simple_cms', array(
    'persistence' => array(
        'phpcr' => array(
            'enabled' => true,
            'basepath' => '/cms/simple',
            'manager_registry' => 'doctrine_phpcr',
            'manager_name' => null,
            'document_class' => 'Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page',
            'use_sonata_admin' => true,
            'sonata_admin' => array(
                'sort' => 'asc',
            ),
        ),
    ),
    'use_menu' => false,
    'ivory_ckeditor' => array(
        'config_name' => 'my_custom_toolbar',
    ),
));
