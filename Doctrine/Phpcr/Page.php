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

use Symfony\Cmf\Bundle\SimpleCmsBundle\Model\Page as ModelPage;

/**
 * {@inheritDoc}
 */
class Page extends ModelPage
{
    public $node;
}
