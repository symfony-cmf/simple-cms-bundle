<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Admin;

use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;
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
            ->add('createDate', 'date')
            ->add('publishStartDate', 'date')
            ->add('publishEndDate', 'date')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('parent', 'doctrine_phpcr_type_tree_model', array('choice_list' => array(), 'root_node' => $this->root))
                ->add('name', 'text', array('label' => 'Last URL part'))
                ->add('label', null, array('required' => false, 'label' => 'Menu label'))
                ->add('title', null, array('label' => 'Page Title'))
                ->add('createDate', null, array('label' => 'Create date'))
                ->add('publishStartDate', null, array('required' => false, 'label' => 'Start date'))
                ->add('publishEndDate', null, array('required' => false, 'label' => 'End date'))
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
