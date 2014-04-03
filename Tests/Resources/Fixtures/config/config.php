<?php

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
            )
        ),
    ),
    'use_menu' => false,
));
