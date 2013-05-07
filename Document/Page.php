<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

use Knp\Menu\NodeInterface;

use Symfony\Cmf\Component\Routing\RouteAwareInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Document\Route;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowInterface;

/**
 * This document is a route, a menu node and a content document with publish
 * workflow all at the same time.
 *
 * Additionally you can store "extra" string values in it for application
 * specific purposes.
 *
 * @PHPCRODM\Document
 */
class Page extends Route implements RouteAwareInterface, NodeInterface, PublishWorkflowInterface
{
    /**
     * @PHPCRODM\Node
     */
    public $node;

    /**
     * @Assert\NotBlank
     * @PHPCRODM\String()
     */
    public $title;

    /**
     * @PHPCRODM\String()
     */
    protected $label;

    /**
     * @PHPCRODM\String()
     */
    protected $body;

    /**
     * @PHPCRODM\Date()
     */
    protected $createDate;

    /**
     * @PHPCRODM\Date()
     */
    protected $publishStartDate;

    /**
     * @PHPCRODM\Date()
     */
    protected $publishEndDate;

    /**
     * @PHPCRODM\Boolean()
     */
    protected $publishable;

    /**
     * @PHPCRODM\String(multivalue=true)
     */
    protected $tags = array();

    /**
     * Extra values an application can store along with a page
     *
     * @PHPCRODM\String(assoc="")
     */
    protected $extras;

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
    public function getRouteContent()
    {
        return $this;
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
        return $this->publishStartDate ? $this->publishStartDate : $this->createDate;
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

    /**
     * Publish workflow: Get the publish start date
     *
     * {@inheritDoc}
     */
    public function getPublishStartDate()
    {
        return $this->publishStartDate;
    }

    /**
     * Publish workflow: Set the publish start date
     *
     * {@inheritDoc}
     */
    public function setPublishStartDate(\DateTime $publishStartDate = null)
    {
        $this->publishStartDate = $publishStartDate;
    }

    /**
     * Publish workflow: Get the publish end date
     *
     * {@inheritDoc}
     */
    public function getPublishEndDate()
    {
        return $this->publishEndDate;
    }

    /**
     * Publish workflow: Set the publish end date
     *
     * {@inheritDoc}
     */
    public function setPublishEndDate(\DateTime $publishEndDate = null)
    {
        $this->publishEndDate = $publishEndDate;
    }

    /**
     * Publish workflow: Gets whether page is publishable
     *
     * {@inheritDoc}
     */
    public function isPublishable()
    {
        return $this->publishable;
    }

    /**
     * Publish workflow: Sets whether the page is publishable
     *
     * @param $publishable
     */
    public function setPublishable($publishable)
    {
        $this->publishable = $publishable;
    }

    /**
     * Content method: Get tags of this page
     *
     * @return \Traversable list of tag strings
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Content method: Set tags of this page
     *
     * @param $tags \Traversable list of tag strings
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Get extras - a flat key-value hashmap
     *
     * @return array with only string values
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * Set extras - applications can store additional information on a page
     * without needing to extend the document class.
     *
     * @param array $extras a flat key-value hashmap. The values are cast to
     *      string as PHPCR stores multivalue data with only one data type for
     *      all values.
     */
    public function setExtras($extras)
    {
        foreach ($extras as $key => $value) {
            $extras[$key] = (string) $value;
        }

        $this->extras = $extras;
    }

    /**
     * Add a single key - value pair to extras
     *
     * @param string $key
     * @param string $value - if this is not a string it is cast to one
     */
    public function addExtra($key, $value)
    {
        $this->extras[$key] = (string) $value;
    }

    /**
     * Remove a single key - value pair from extras, if it was set.
     *
     * @param string $key
     */
    public function removeExtra($key)
    {
        if (array_key_exists($key, $this->extras)) {
            unset($this->extras[$key]);
        }
    }

    /**
     * Return a single extras value for the provided key or the $default if
     * the key is not defined.
     *
     * @param string $key
     * @param string|null $default
     *
     * @return string|null The value at $key or if not existing $default
     */
    public function getExtra($key, $default = null)
    {
        return array_key_exists($key, $this->extras) ? $this->extras[$key] : $default;
    }

}
