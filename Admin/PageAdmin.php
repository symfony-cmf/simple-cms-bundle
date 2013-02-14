<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Admin;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Document\MultilangPage;
use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin as BaseAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;

class PageAdmin extends BaseAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('path', 'text')
            ->add('title', 'text')
            ->add('label', 'text')
            ->add('name', 'text')
            ->add('createDate', 'date')
            ->add('publishStartDate', 'date')
            ->add('publishEndDate', 'date')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('parent', 'doctrine_phpcr_odm_tree', array('choice_list' => array(), 'root_node' => $this->root))
            ->add('name', 'text')
            ->add('label', null, array('required' => false))
            ->add('title')
            ->add('createDate')
            ->add('publishStartDate', null, array('required' => false))
            ->add('publishEndDate', null, array('required' => false))
            ->add('body', 'textarea')
        ;
    }

    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!in_array($action, array('edit', 'create'))) {
            return;
        }

        $menu->addChild(
            $this->trans('sidemenu.publish_start_end_date')
        );

        $menu->addChild(
            $this->trans('sidemenu.end_date_disable')
        );

        if ('edit' == $action) {
            $page = $this->getSubject();
            $uri = $this->routeGenerator->generate($page);
            if ($page instanceof MultilangPage) {
                $uri.= '#'.$page->getTitle();
            }

            $menu->addChild(
                $this->trans('sidemenu.review'),
                array('uri' => $uri)
            );
        }

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
