<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Model;

use \LogicException;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

use Knp\Menu\NodeInterface;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

/**
 * This document is a route, a menu node and a content document with publish
 * workflow all at the same time.
 */
class PageBase extends Route implements
    RouteReferrersReadInterface, // this must not be the write interface, it would make no sense
    NodeInterface
{

}
