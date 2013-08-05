<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Model;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishableInterface;
use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishTimePeriodInterface;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;

/**
 * This is the standard Page document.
 *
 * It adds the following to the base document
 *
 * - Translatable
 * - Publish Workflow
 * - Tags
 *
 * Additionally you can store "extra" string values in it for application
 * specific purposes.
 *
 * @PHPCRODM\Document(translator="attribute")
 */
class Page extends PageBase implements
    PublishTimePeriodInterface,
    PublishableInterface,
    TranslatableInterface
{
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
     * @var array
     */
    protected $tags = array();

    /**
     * Extra values an application can store along with a page
     */
    protected $extras;

    /**
     * Overwrite to be able to create route without pattern
     *
     * @param Boolean $addFormatPattern whether to add ".{_format}" to the route pattern
     *                                  also implicitly sets a default/require on "_format" to "html"
     * @param Boolean $addLocalePattern whether to add "/{_locale}" to the route pattern
     */
    public function __construct($addFormatPattern = false, $addLocalePattern = true)
    {
        parent::__construct($addFormatPattern);
        $this->addLocalePattern = $addLocalePattern;
    }


    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritDoc}
     *
     * automatically prepend the _locale to the pattern
     *
     * @see MultilangRouteProvider::getCandidates()
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
     * {@inheritDoc}
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
