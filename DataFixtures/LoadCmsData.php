<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use PHPCR\Util\NodeHelper;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Yaml\Parser;

abstract class LoadCmsData extends ContainerAware implements FixtureInterface, OrderedFixtureInterface
{
    abstract protected function getData();

    public function load(ObjectManager $dm)
    {
        $session = $dm->getPhpcrSession();
        $basepath = $this->container->getParameter('symfony_cmf_content.static_basepath');
        NodeHelper::createPath($session, $basepath);

        $basepath = $this->container->getParameter('symfony_cmf_simple_cms.basepath');
        NodeHelper::createPath($session, $basepath);

        $data = $this->getData();

        $defaultClass = $this->container->getParameter('symfony_cmf_simple_cms.locales')
            ? 'Symfony\Cmf\Bundle\SimpleCmsBundle\Document\MultilangPage'
            : 'Symfony\Cmf\Bundle\SimpleCmsBundle\Document\Page'
        ;

        $class = isset($overview['class']) ? $overview['class'] : $defaultClass;

        $paths = array('/' => $basepath);
        foreach ($data['static'] as $overview) {
            $overview['parent'] = empty($overview['parent']) ? '/' : $overview['parent'];
            $path = $paths[$overview['parent']].($overview['name'] ? '/'.$overview['name'] : '');
            $paths[$overview['parent'].$overview['name']] = $path;

            $page = $dm->find($class, $path);
            if (!$page) {
                $page = new $class();
                $page->setPath($path);
            }

            $overview['formats'] = isset($overview['formats']) ? $overview['formats'] : array('html');
            $page->setDefault('_format', reset($overview['formats']));
            $page->setRequirement('_format', implode('|', $overview['formats']));

            if (!empty($overview['template'])) {
                $page->setDefault(RouteObjectInterface::TEMPLATE_NAME, $overview['template']);
            }

            if (!empty($overview['controller'])) {
                $page->setDefault(RouteObjectInterface::CONTROLLER_NAME, $overview['controller']);
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
                $page->setBody($overview['content']);
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
