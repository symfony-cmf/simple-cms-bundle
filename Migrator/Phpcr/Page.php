<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Migrator\Phpcr;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

use PHPCR\Util\NodeHelper;
use PHPCR\SessionInterface;

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\Bundle\PHPCRBundle\Migrator\MigratorInterface;
use Doctrine\ODM\PHPCR\DocumentManager;

/**
 * Doctrine migrator for pages
 *
 * Provides a way of creating and maintaining pages from YAML files.
 */
class Page implements MigratorInterface
{
    /**
     * @var PHPCR\SessionInterface
     */
    protected $session;

    /*
     * @var Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager
     */
    protected $dm;

    protected $basepath;

    protected $dataDir;

    public function __construct(ManagerRegistry $registry, $basepath, $dataDir)
    {
        $this->dm = $registry->getManager();
        $this->session = $registry->getConnection();
        $this->basepath = $basepath;
        $this->dataDir = $dataDir;
    }

    public function init(SessionInterface $session, OutputInterface $output)
    {
        $this->session = $session;
        $this->output = $output;
    }

    protected function createPageInstance($className)
    {
        return new $className(true);
    }

    public function migrate($path = '/', $depth = -1)
    {
        if (0 !== strpos($path, $this->basepath)) {
            throw new \RuntimeException("The provided identifier '$path' does not start with the base path '{$this->basepath}'");
        }

        $yaml = new Parser();
        $contentPath = substr($path, strlen($this->basepath));
        $data = $yaml->parse(file_get_contents($this->dataDir.$contentPath.'.yml'));

        NodeHelper::createPath($this->session, preg_replace('#/[^/]*$#', '', $this->basepath));

        $class = isset($data['class']) ? $data['class'] : 'Symfony\\Cmf\\Bundle\\SimpleCmsBundle\\Doctrine\\Phpcr\\Page';

        $page = $this->dm->find($class, $path);
        if (!$page) {
            $page = $this->createPageInstance($class);
            $page->setId($path);
        }

        if (isset($data['formats'])) {
            $page->setDefault('_format', reset($data['formats']));
            $page->setRequirement('_format', implode('|', $data['formats']));
        }

        if (!empty($data['template'])) {
            $page->setDefault(RouteObjectInterface::TEMPLATE_NAME, $data['template']);
        }

        if (!empty($data['controller'])) {
            $page->setDefault(RouteObjectInterface::CONTROLLER_NAME, $data['controller']);
        }

        if (!empty($data['options'])) {
            $page->setOptions($data['options']);
        }

        $this->dm->persist($page);

        if (is_array($data['title'])) {
            $page->setAddLocalePattern(true);
            foreach ($data['title'] as $locale => $title) {
                $page->setTitle($title);
                if (isset($data['label'][$locale]) && $data['label'][$locale]) {
                    $page->setLabel($data['label'][$locale]);
                } elseif (!isset($data['label'][$locale])) {
                    $page->setLabel($title);
                }
                $page->setBody($data['body'][$locale]);
                $this->dm->bindTranslation($page, $locale);
            }
        } else {
            $page->setTitle($data['title']);
            if (isset($data['label'])) {
                if ($data['label']) {
                    $page->setLabel($data['label']);
                }
            } elseif (!isset($data['label'])) {
                $page->setLabel($data['title']);
            }
            $page->setBody($data['body']);
        }

        if (isset($data['create_date'])) {
            $page->setCreateDate(date_create_from_format('U', strtotime($data['create_date'])));
        }

        if (isset($data['publish_start_date'])) {
            $page->setPublishStartDate(date_create_from_format('U', strtotime($data['publish_start_date'])));
        }

        if (isset($data['publish_end_date'])) {
            $page->setPublishEndDate(date_create_from_format('U', strtotime($data['publish_end_date'])));
        }

        $this->dm->flush();

        return 0;
    }
}
