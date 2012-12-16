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
            ->add('parent', 'doctrine_phpcr_type_tree_model', array('choice_list' => array(), 'root_node' => $this->root))
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

    public function prePersist($object)
    {
        $this->ensureOrderByDate();
    }

    public function preUpdate($object)
    {
        $this->ensureOrderByDate();
    }

    protected function ensureOrderByDate()
    {
        $dm = $this->getModelManager()->getDocumentManager();

        $page = $dm->find(null, $this->root);
        $items = $page->getChildren();

        $itemsByDate = array();
        foreach ($items as $item) {
            $itemsByDate[$item->getDate()->format('U')][$item->getCreateDate()->format('U')][] = $item;
        }

        ksort($itemsByDate);
        $sortedItems = array();
        foreach ($itemsByDate as $itemsForDate) {
            ksort($itemsForDate);
            foreach ($itemsForDate as $itemsForCreateDate) {
                foreach ($itemsForCreateDate as $item) {
                    $sortedItems[$item->getName()] = $item;
                }
            }
        }

        if ($sortedItems !== $items->getKeys()) {
            $page->setChildren($sortedItems);
        }
    }
}
