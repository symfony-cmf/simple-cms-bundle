<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RouteProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr\Page;

/**
 * Provider to load routes from PHPCR-ODM by locale
 *
 * @author smith@pooteeweet.org
 */
class PageRouteProvider extends RouteProvider
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

    public function setLocales($locales = array())
    {
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
            $this->getObjectManager()->getLocaleChooserStrategy()->setLocale($this->locale);
        }

        return parent::getCandidates($url);
    }

    protected function configureLocale($route)
    {
        if ($this->getObjectManager()->isDocumentTranslatable($route)) {
            // add locale requirement
            if (!$route->getRequirement('_locale')) {
                $locales = $this->getObjectManager()->getLocalesFor($route, true);
                $route->setRequirement('_locale', implode('|', $locales));
            }
        }
    }

    public function getRouteCollectionForRequest(Request $request)
    {
        $collection = parent::getRouteCollectionForRequest($request);
        foreach ($collection as $route) {
            $this->configureLocale($route);
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name, $parameters = array())
    {
        $route = parent::getRouteByName($name, $parameters);
        $this->configureLocale($route);

        return $route;
    }
}
