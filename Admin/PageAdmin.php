<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Admin;

use Sonata\DoctrinePHPCRAdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page;

class PageAdmin extends Admin
{
    protected $translationDomain = 'CmfSimpleCmsBundle';

    protected $baseRouteName = 'cmf_simplecms_page';
    protected $baseRoutePattern = '/cmf/simplecms/page';

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
            ->with('form.group_general')
            ->add(
                'parent',
                'doctrine_phpcr_odm_tree',
                array('choice_list' => array(), 'select_root_node' => true, 'root_node' => $this->getRootPath())
            )
            ->add('name', 'text')
            ->add('label', null, array('required' => false))
            ->add('title')
            ->add('createDate')
            ->add('addFormatPattern', null, array('required' => false, 'help' => 'form.help_add_format_pattern'))
            ->add('addTrailingSlash', null, array('required' => false, 'help' => 'form.help_add_trailing_slash'))
            ->add('addLocalePattern', null, array('required' => false, 'help' => 'form.help_add_locale_pattern'))
            ->add('body', 'textarea')
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
        $items = $page->getParent()->getChildren();

        $itemsByDate = array();
        /** @var $item Page */
        foreach ($items as $item) {
            $itemsByDate[$item->getDate()->format('U')][$item->getCreateDate()->format('U')][] = $item;
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
            foreach ($itemsForDate as $itemsForCreateDate) {
                foreach ($itemsForCreateDate as $item) {
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
}
