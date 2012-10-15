<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Document;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\RouteRepository;

/**
 * Repository to load routes from PHPCR-ODM by locale
 *
 * This is <strong>NOT</strong> not a doctrine repository but just the proxy
 * for the DynamicRouter implementing RouteRepositoryInterface
 *
 * @author smith@pooteeweet.org
 */
class MultilangRouteRepository extends RouteRepository
{
    /**
     * Locales
     *
     * @var array
     */
    protected $locales = array();

    /**
     * The detected locale
     *
     * @var string
     */
    private $locale;

    public function __construct(ObjectManager $dm, $className = null, $locales = array())
    {
        parent::__construct($dm, $className);
        $this->locales = $locales;
    }

    protected function getCandidates($url)
    {
        $dirs = explode('/', ltrim($url, '/'));
        if (isset($dirs[0]) && in_array($dirs[0], $this->locales)) {
            $this->locale = $dirs[0];
            array_shift($dirs);
            $url = '/'.implode('/', $dirs);

            // the normal listener "waits" until the routing completes
            // as the locale could be defined inside the route
            $this->dm->getLocaleChooserStrategy()->setLocale($this->locale);
        }

        return parent::getCandidates($url);
    }

    public function findManyByUrl($url)
    {
        $collection = parent::findManyByUrl($url);
        foreach ($collection as $route) {
            $locales = $this->dm->getLocalesFor($route, true);
            if (!$route->getRequirement('_locale')) {
                $route->setRequirement('_locale', implode('|', $locales));
            }
        }

        return $collection;
    }
}