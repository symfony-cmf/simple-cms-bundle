<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Document;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;

/**
 * @PHPCRODM\Document
 *
 * provides multi language support when using MultilangRouteProvider
 */
class MultilangRedirectRoute extends RedirectRoute
{
    /**
     * {@inheritDoc}
     *
     * automatically prepend the _locale to the pattern
     *
     * @see MultilangRouteProvider::getCandidates()
     */
    public function getStaticPrefix()
    {
        $prefix = $this->getPrefix();
        $path = substr($this->getId(), strlen($prefix));
        $path = $prefix.'/{_locale}'.$path;

        return $this->generateStaticPrefix($path, $this->idPrefix);
    }
}
