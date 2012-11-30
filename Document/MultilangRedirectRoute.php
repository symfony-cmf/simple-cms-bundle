<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Document;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\RedirectRoute;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;

/**
 * @PHPCRODM\Document
 */
class MultilangRedirectRoute extends RedirectRoute
{
    /**
     * {@inheritDoc}
     *
     * automatically prepend the _locale to the pattern
     *
     * @see MultilangRouteRepository::getCandidates()
     */
    public function getStaticPrefix()
    {
        $prefix = $this->getPrefix();
        $path = substr($this->getPath(), strlen($prefix));
        $path = $prefix.'/{_locale}'.$path;

        return $this->generateStaticPrefix($path, $this->idPrefix);
    }
}
