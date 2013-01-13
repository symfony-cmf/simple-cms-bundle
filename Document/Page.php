<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Knp\Menu\NodeInterface;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Cmf\Component\Routing\RouteAwareInterface;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowInterface;

/**
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
     * @PHPCRODM\String(multivalue=true)
     */
    protected $tags = array();

    /**
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
     * @return array of route objects that point to this content
     */
    public function getRoutes()
    {
        return array($this);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRouteContent()
    {
        return $this;
    }

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

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the date
     */
    public function getDate()
    {
        return $this->publishStartDate ? $this->publishStartDate : $this->createDate;
    }

    /**
     * Get the create date
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    public function setCreateDate(\DateTime $createDate = null)
    {
        $this->createDate = $createDate;
    }

    /**
     * Get the publish start date
     */
    public function getPublishStartDate()
    {
        return $this->publishStartDate;
    }

    public function setPublishStartDate(\DateTime $publishStartDate = null)
    {
        $this->publishStartDate = $publishStartDate;
    }

    /**
     * Get the publish end date
     */
    public function getPublishEndDate()
    {
        return $this->publishEndDate;
    }

    public function setPublishEndDate(\DateTime $publishEndDate = null)
    {
        $this->publishEndDate = $publishEndDate;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Get extras
     *
     * @return array
     */
    public function getExtras()
    {
        return $this->extras;
    }

    /**
     * Set extras
     *
     * @param array $extras
     */
    public function setExtras($extras)
    {
        $this->extras = $extras;
    }

    /**
     * Add a single key value pair to extras
     *
     * @param string $key
     * @param mixed $value
     */
    public function addExtra($key, $value)
    {
        $this->extras[$key] = $value;
    }

    /**
     * Remove a single key value pair from extras
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
     * Return a single extras value for the provided key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getExtra($key, $default = null)
    {
        return array_key_exists($key, $this->extras) ? $this->extras[$key] : $default;
    }

}
