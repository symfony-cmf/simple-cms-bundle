<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Model;

use \LogicException;
use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

use Knp\Menu\NodeInterface;

use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

/**
 * This document is a route, a menu node and a content document with publish
 * workflow all at the same time.
 *
 * @PHPCRODM\Document
 */
class PageBase extends Route implements
    RouteReferrersReadInterface, // this must not be the write interface, it would make no sense
    NodeInterface
{
    public $node;

    /**
     * @Assert\NotBlank
     */
    public $title;

    protected $label;

    protected $body;

    /**
     * @PHPCRODM\Date()
     */
    protected $createDate;

    /**
     * Overwrite to be able to create route without pattern
     *
     * @param Boolean $addFormatPattern if to add ".{_format}" to the route pattern
     *                                  also implicitly sets a default/require on "_format" to "html"
     */
    public function __construct($addFormatPattern = false)
    {
        parent::__construct($addFormatPattern);
        $this->createDate = new \DateTime();
    }

    /**
     * Content method: Get the routes that point to this content
     *
     * {@inheritDoc}
     */
    public function getRoutes()
    {
        return array($this);
    }

    /**
     * Menu method: Get child menu nodes.
     *
     * @return ArrayCollection the child nodes
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Menu method: List of child menu nodes
     *
     * @param object[] $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Route method: The content of this route is the object itself.
     *
     * {@inheritDoc}
     */
    public function getContent()
    {
        return $this;
    }

    /**
     * Never call this, it makes no sense. The SimpleCms Page is its own
     * content.
     *
     * @param $document
     *
     * @throws LogicException
     */
    public function setContent($document)
    {
        throw new LogicException('Do not set a content for the redirect route. It is its own content.');
    }

    /**
     * Route method and Menu method - provides menu options merged with the
     * route options
     *
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return parent::getOptions() + array(
//            'uri' => $this->getUri(),
//            'route' => $this->getRoute(),
            'label' => $this->getLabel(),
            'attributes' => array(),
            'childrenAttributes' => array(),
            'display' => ! empty($this->label),
            'displayChildren' => true,
            'content' => $this,
            'routeParameters' => array(),
            'routeAbsolute' => false,
            'linkAttributes' => array(),
            'labelAttributes' => array(),
        );
    }

    /**
     * Menu method: set the menu label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Menu method: get the label for the menu
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Content method: set the page title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Content method: get the page title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Content method: set the page body
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Content method: get the page body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the date this page was published.
     *
     * If the publish workflow is used, this is the publish start date rather
     * than the creation date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get the creation date
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Overwrite the creation date manually
     *
     * On creation of a Page, the createDate is automatically set to the
     * current time.
     *
     * @param \DateTime $createDate
     */
    public function setCreateDate(\DateTime $createDate = null)
    {
        $this->createDate = $createDate;
    }

}
