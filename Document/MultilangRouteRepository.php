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
     * The locale that does not need to be included in the URL
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * The detected locale
     *
     * @var string
     */
    private $locale;

    public function __construct(ObjectManager $dm, $className = null, $locales = array(), $defaultLocale = null)
    {
        parent::__construct($dm, $className);
        $this->locales = $locales;
        $this->defaultLocale = $defaultLocale;
    }

    protected function getCandidates($url)
    {
        $dirs = explode('/', ltrim($url, '/'));
        if (isset($dirs[0]) && $this->defaultLocale !== $dirs[0] && in_array($dirs[0], $this->locales)) {
            $this->locale = $dirs[0];
            array_shift($dirs);
            $url = '/'.implode('/', $dirs);
        } else {
            $this->locale = $this->defaultLocale;
        }

        // TODO empty default locale should attempt to redirect using the users preferred locale?

        $this->dm->getLocaleChooserStrategy()->setLocale($this->locale);

        return parent::getCandidates($url);
    }

    /**
     * {@inheritDoc}
     */
    public function findManyByUrl($url)
    {
        $routes = parent::findManyByUrl($url);

        if ($this->locale && $routes) {
            foreach ($routes as $route) {
                $route->setLocale($this->locale);
            }
        }

        return $routes;
    }
}
