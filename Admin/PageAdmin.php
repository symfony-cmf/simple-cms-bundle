<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;
use Symfony\Cmf\Bundle\RoutingBundle\Admin\RouteAdmin;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page;

class PageAdmin extends RouteAdmin
{
    protected $translationDomain = 'CmfSimpleCmsBundle';

    private $sortOrder = false;

    public function setSortOrder($sortOrder)
    {
        if (! in_array($sortOrder, array(false, 'asc', 'desc'))) {
            throw new \InvalidArgumentException($sortOrder);
        }
        $this->sortOrder = $sortOrder;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('path', 'text')
            ->addIdentifier('title', 'text')
            ->add('label', 'text')
            ->add('name', 'text')
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper->remove('content');

        // remap to routeOptions
        $formMapper->remove('options');

        $formMapper
            ->with('form.group_general', array(
                'translation_domain' => 'CmfSimpleCmsBundle',
            ))
                ->add('label', null, array('required' => false))
                ->add('title')
                ->add('body', 'textarea')
            ->end()
            ->with('form.group_advanced', array(
                'translation_domain' => 'CmfRoutingBundle',
            ))
                ->add(
                    'routeOptions',
                    'sonata_type_immutable_array',
                    array('keys' => $this->configureFieldsForOptions($this->getSubject()->getRouteOptions()), 'label' => 'form.label_options'),
                    array('help' => 'form.help_options')
                )
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', 'doctrine_phpcr_string')
            ->add('name',  'doctrine_phpcr_nodename')
        ;
    }

    public function getExportFormats()
    {
        return array();
    }

    public function prePersist($object)
    {
        if ($this->sortOrder) {
            $this->ensureOrderByDate($object);
        }
    }

    public function preUpdate($object)
    {
        if ($this->sortOrder) {
            $this->ensureOrderByDate($object);
        }
    }

    /**
     * @param Page $page
     */
    protected function ensureOrderByDate($page)
    {
        $items = $page->getParentDocument()->getChildren();

        $itemsByDate = array();
        /** @var $item Page */
        foreach ($items as $item) {
            $itemsByDate[$item->getDate()->format('U')][$item->getPublishStartDate()->format('U')][] = $item;
        }

        if ('asc' == $this->sortOrder) {
            ksort($itemsByDate);
        } else {
            krsort($itemsByDate);
        }
        $sortedItems = array();
        foreach ($itemsByDate as $itemsForDate) {
            if ('asc' == $this->sortOrder) {
                ksort($itemsForDate);
            } else {
                krsort($itemsForDate);
            }
            foreach ($itemsForDate as $itemsForPublishDate) {
                foreach ($itemsForPublishDate as $item) {
                    $sortedItems[$item->getName()] = $item;
                }
            }
        }

        if ($sortedItems !== $items->getKeys()) {
            $items->clear();
            foreach ($sortedItems as $key => $item) {
                $items[$key] = $item;
            }
        }
    }

    public function toString($object)
    {
        return $object instanceof Page && $object->getTitle()
            ? $object->getTitle()
            : $this->trans('link_add', array(), 'SonataAdminBundle')
        ;
    }
}
