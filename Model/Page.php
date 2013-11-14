<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Model;

use LogicException;
use Knp\Menu\NodeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableInterface;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * This is the standard Simple CMS Page document.
 *
 * It supports:
 *
 * - Multilang
 * - Publish Workflow
 *
 * Additionally you can store "extra" string values in it for application
 * specific purposes.
 */
class Page extends Route implements
    NodeInterface,
    RouteReferrersReadInterface, // this must not be the write interface, it would make no sense
    PublishTimePeriodInterface,
    PublishableInterface,
    TranslatableInterface
{
    /**
     * @Assert\NotBlank
     */
    protected $title;

    /**
     * Menu label.
     *
     * @var string
     */
    protected $label = '';

    /**
     * HTML attributes to add to the individual menu element.
     *
     * e.g. array('class' => 'foobar', 'style' => 'bar: foo')
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * HTML attributes to add to the children list element.
     *
     * e.g. array('class' => 'foobar', 'style' => 'bar: foo')
     *
     * @var array
     */
    protected $childrenAttributes = array();

    /**
     * HTML attributes to add to items link.
     *
     * e.g. array('class' => 'foobar', 'style' => 'bar: foo')
     *
     * @var array
     */
    protected $linkAttributes = array();

    /**
     * HTML attributes to add to the items label.
     *
     * e.g. array('class' => 'foobar', 'style' => 'bar: foo')
     *
     * @var array
     */
    protected $labelAttributes = array();

    /**
     * Set to false to not render a menu item for this.
     *
     * @var boolean
     */
    protected $display = true;

    /**
     * Set to false to not render the child menu items of this page.
     *
     * @var boolean
     */
    protected $displayChildren = true;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var \DateTime
     */
    protected $createDate;

    /**
     * @var \DateTime
     */
    protected $publishStartDate;

    /**
     * @var \DateTime
     */
    protected $publishEndDate;

    /**
     * @var boolean
     */
    protected $publishable = true;

    /**
     * @var boolean
     */
    protected $addLocalePattern;

    /**
     * @var string
     */
    protected $locale;

    /**
     * Extra values an application can store along with a page
     * @var array
     */
    protected $extras = array();

    /**
     * Overwrite to be able to create route without pattern
     *
     * @param Boolean $addFormatPattern whether to add ".{_format}" to the route pattern
     *                                  also implicitly sets a default/require on "_format" to "html"
     * @param Boolean $addTrailingSlash whether to add a trailing slash to the route, defaults to not add one
     * @param Boolean $addLocalePattern whether to add "/{_locale}" to the route pattern
     */
    public function __construct($addFormatPattern = false, $addTrailingSlash = false, $addLocalePattern = false)
    {
        parent::__construct($addFormatPattern, $addTrailingSlash);
        $this->addLocalePattern = $addLocalePattern;
        $this->createDate = new \DateTime();
    }

    public function getAddLocalePattern()
    {
        return $this->addLocalePattern;
    }

    public function setAddLocalePattern($addLocalePattern)
    {
        $this->addLocalePattern = $addLocalePattern;
    }

    /**
     * {@inheritDoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritDoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritDoc}
     *
     * automatically prepend the _locale to the pattern
     *
     * @see PageRouteProvider::getCandidates()
     */
    public function getStaticPrefix()
    {
        if (!$this->addLocalePattern) {
            return parent::getStaticPrefix();
        }

        $prefix = $this->getPrefix();
        $path = substr(parent::getId(), strlen($prefix));
        $path = $prefix.'/{_locale}'.$path;

        return $this->generateStaticPrefix($path, $this->idPrefix);
    }

    /**
     * Get the "date" of this page, which is the publishStartDate if set,
     * otherwise the createDate.
     */
    public function getDate()
    {
        return $this->publishStartDate ? $this->publishStartDate : $this->createDate;
    }

    /**
     * {@inheritDoc}
     */
    public function getPublishStartDate()
    {
        return $this->publishStartDate;
    }

    /**
     * {@inheritDoc}
     */
    public function setPublishStartDate(\DateTime $publishStartDate = null)
    {
        $this->publishStartDate = $publishStartDate;
    }

    /**
     * {@inheritDoc}
     */
    public function getPublishEndDate()
    {
        return $this->publishEndDate;
    }

    /**
     * {@inheritDoc}
     */
    public function setPublishEndDate(\DateTime $publishEndDate = null)
    {
        $this->publishEndDate = $publishEndDate;
    }

    /**
     * {@inheritDoc}
     */
    public function isPublishable()
    {
        return $this->publishable;
    }

    /**
     * {@inheritDoc}
     */
    public function setPublishable($publishable)
    {
        $this->publishable = $publishable;
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
     * @param string      $key
     * @param string|null $default
     *
     * @return string|null The value at $key or if not existing $default
     */
    public function getExtra($key, $default = null)
    {
        return array_key_exists($key, $this->extras) ? $this->extras[$key] : $default;
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

    /**
     * {@inheritDoc}
     */
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
     * Return the attributes associated with this menu node
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the attributes associated with this menu node
     *
     * @param $attributes array
     *
     * @return Page The current Page instance
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Return the given attribute, optionally specifying a default value
     *
     * @param string $name    The name of the attribute to return
     * @param string $default The value to return if the attribute doesn't exist
     *
     * @return string
     */
    public function getAttribute($name, $default = null)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * Set the named attribute
     *
     * @param string $name  attribute name
     * @param string $value attribute value
     *
     * @return Page The current Page instance
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Return the children attributes
     *
     * @return array
     */
    public function getChildrenAttributes()
    {
        return $this->childrenAttributes;
    }

    /**
     * Set the children attributes
     *
     * @param array $attributes
     *
     * @return Page The current Page instance
     */
    public function setChildrenAttributes(array $attributes)
    {
        $this->childrenAttributes = $attributes;

        return $this;
    }

    /**
     * Get the link HTML attributes.
     *
     * @return array
     */
    public function getLinkAttributes()
    {
        return $this->linkAttributes;
    }

    /**
     * Set the link HTML attributes as associative array.
     *
     * @param array $linkAttributes
     *
     * @return Page The current Page instance
     */
    public function setLinkAttributes($linkAttributes)
    {
        $this->linkAttributes = $linkAttributes;

        return $this;
    }

    /**
     * Get the label HTML attributes.
     *
     * @return array
     */
    public function getLabelAttributes()
    {
        return $this->labelAttributes;
    }

    /**
     * Set the label HTML attributes as associative array.
     *
     * @param array $labelAttributes
     *
     * @return Page The current Page instance
     */
    public function setLabelAttributes($labelAttributes)
    {
        $this->labelAttributes = $labelAttributes;

        return $this;
    }

    /**
     * Whether to display the menu item for this.
     *
     * @return boolean
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Set whether to display the menu item for this.
     *
     * @param boolean $display
     *
     * @return Page The current Page instance
     */
    public function setDisplay($display)
    {
        $this->display = $display;

        return $this;
    }

    /**
     * Whether to display the child menu items of this page.
     *
     * @return boolean
     */
    public function getDisplayChildren()
    {
        return $this->displayChildren;
    }

    /**
     * Set whether to display the child menu items of this page.
     *
     * @param boolean $displayChildren
     *
     * @return Page The current Page instance
     */
    public function setDisplayChildren($displayChildren)
    {
        $this->displayChildren = $displayChildren;

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
            'label' => $this->getLabel(),
            'attributes' => $this->getAttributes(),
            'childrenAttributes' => $this->getChildrenAttributes(),
            'display' => $this->display,
            'displayChildren' => $this->displayChildren,
            'routeParameters' => array(),
            'routeAbsolute' => false,
            'linkAttributes' => $this->linkAttributes,
            'labelAttributes' => $this->labelAttributes,
            'content' => $this,
        );
    }
}
