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
    protected $publishStartDate;

    /**
     * @PHPCRODM\Date()
     */
    protected $publishEndDate;

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

    /**
     * @PHPCRODM\PreUpdate
     * @PHPCRODM\PrePersist
     */
    public function prepareArrays()
    {
        parent::prepareArrays();

        // avoid storing the menu item options
        $options = parent::getOptions();
        // avoid storing the default value for the compiler, in case this ever changes in code
        // would be nice if those where class constants of the symfony route instead of hardcoded strings
        if ('Symfony\\Component\\Routing\\RouteCompiler' == $options['compiler_class']) {
            unset($options['compiler_class']);
        }
        $this->optionsKeys = array_keys($options);
        $this->optionsValues = array_values($options);
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
}
