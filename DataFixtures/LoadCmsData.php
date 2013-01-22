<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use PHPCR\Util\NodeHelper;

use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

abstract class LoadCmsData extends ContainerAware implements FixtureInterface, OrderedFixtureInterface
{
    protected $defaultClass = array(
        'multilangpage' => 'Symfony\Cmf\Bundle\SimpleCmsBundle\Document\MultilangPage',
        'page' => 'Symfony\Cmf\Bundle\SimpleCmsBundle\Document\Page'
    );

    abstract protected function getData();

    /**
     * @param $className
     * @return \Symfony\Cmf\Bundle\SimpleCmsBundle\Document\Page
     */
    protected function createPageInstance($className)
    {
        return new $className(true);
    }

    protected function getBasePath()
    {
        return $this->container->getParameter('symfony_cmf_simple_cms.basepath');
    }

    protected function getDefaultClass()
    {
        return $this->container->getParameter('symfony_cmf_simple_cms.locales')
            ? $this->defaultClass['multilangpage'] : $this->defaultClass['page'];
    }

    public function load(ObjectManager $dm)
    {
        $session = $dm->getPhpcrSession();

        $basepath = $this->getBasePath();
        NodeHelper::createPath($session, preg_replace('#/[^/]*$#', '', $basepath));

        $data = $this->getData();

        $defaultClass = $this->getDefaultClass();

        foreach ($data['static'] as $overview) {
            $class = isset($overview['class']) ? $overview['class'] : $defaultClass;

            $parent = (isset($overview['parent']) ? trim($overview['parent'], '/') : '');
            $name = (isset($overview['name']) ? trim($overview['name'], '/') : '');

            $path = $basepath
                .(empty($parent) ? '' : '/' . $parent)
                .(empty($name) ? '' : '/' . $name);

            $page = $dm->find($class, $path);
            if (!$page) {
                $page = $this->createPageInstance($class);
                $page->setId($path);
            }

            if (isset($overview['formats'])) {
                $page->setDefault('_format', reset($overview['formats']));
                $page->setRequirement('_format', implode('|', $overview['formats']));
            }

            if (!empty($overview['template'])) {
                $page->setDefault(RouteObjectInterface::TEMPLATE_NAME, $overview['template']);
            }

            if (!empty($overview['controller'])) {
                $page->setDefault(RouteObjectInterface::CONTROLLER_NAME, $overview['controller']);
            }

            if (!empty($overview['options'])) {
                $page->setOptions($overview['options']);
            }

            $dm->persist($page);

            if (is_array($overview['title'])) {
                foreach ($overview['title'] as $locale => $title) {
                    $page->setTitle($title);
                    if (isset($overview['label'][$locale]) && $overview['label'][$locale]) {
                        $page->setLabel($overview['label'][$locale]);
                    } elseif (!isset($overview['label'][$locale])) {
                        $page->setLabel($title);
                    }
                    $page->setBody($overview['body'][$locale]);
                    $dm->bindTranslation($page, $locale);
                }
            } else {
                $page->setTitle($overview['title']);
                if (isset($overview['label'])) {
                    if ($overview['label']) {
                        $page->setLabel($overview['label']);
                    }
                } elseif (!isset($overview['label'])) {
                    $page->setLabel($overview['title']);
                }
                $page->setBody($overview['body']);
            }

            if (isset($overview['create_date'])) {
                $page->setCreateDate(date_create_from_format('U', strtotime($overview['create_date'])));
            }

            if (isset($overview['publish_start_date'])) {
                $page->setPublishStartDate(date_create_from_format('U', strtotime($overview['publish_start_date'])));
            }

            if (isset($overview['publish_end_date'])) {
                $page->setPublishEndDate(date_create_from_format('U', strtotime($overview['publish_end_date'])));
            }
        }

        $dm->flush();
    }
}
