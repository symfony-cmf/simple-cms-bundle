<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Admin;

use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class PageAdmin extends Admin
{
    /**
     * Path to the root node of simple pages.
     *
     * @var string
     */
    protected $root;

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
                ->add('parent', 'doctrine_phpcr_type_tree_model', array('choice_list' => array(), 'root_node' => $this->root))
                ->add('name', 'text', array('label'=>'Last URL part'))
                ->add('label', null, array('required'=>false, 'label'=>'Menu label'))
                ->add('title', null, array('label'=>'Page Title'))
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

    public function setRoot($root)
    {
        $this->root = $root;
    }
}
