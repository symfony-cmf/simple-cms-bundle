<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class PageAdmin extends Admin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('path', 'text')
            ->add('title')
            ->add('label')
            ->add('name')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                // TODO: how to make the tree model understand documents as well as paths?
                //->add('parent', 'doctrine_phpcr_type_tree_model', array('class' => 'Symfony\\Cmf\\Bundle\\SimpleCmsBundle\\Document\Page'))
                ->add('label', null, array('required'=>false, 'label'=>'Menu name'))
                ->add('name', 'text')
                ->add('title')
                ->add('body', 'textarea')
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', 'doctrine_phpcr_string')
            ->add('name',  'doctrine_phpcr_string')
            ;
    }

    public function getExportFormats()
    {
        return array();
    }

}
