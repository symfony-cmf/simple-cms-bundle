<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Doctrine\Phpcr;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;

/**
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
