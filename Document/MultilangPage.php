<?php

namespace Symfony\Cmf\Bundle\SimpleCmsBundle\Document;

use Doctrine\ODM\PHPCR\Mapping\Annotations as PHPCRODM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @PHPCRODM\Document(translator="attribute")
 */
class MultilangPage extends Page
{
    /**
     * @PHPCRODM\Boolean()
     */
    protected $addLocalePattern;

    /**
     * Overwrite to be able to create route without pattern
     *
     * @param Boolean $addFormatPattern if to add ".{_format}" to the route pattern
     *                                  also implicitly sets a default/require on "_format" to "html"
     */
    public function __construct($addFormatPattern = false, $addLocalePattern = true)
    {
        parent::__construct($addFormatPattern);
        $this->addLocalePattern = $addLocalePattern;
    }

    /**
     * @Assert\NotBlank
     * @PHPCRODM\String(translated=true)
     */
    public $title;

    /**
     * @PHPCRODM\String(translated=true)
     */
    public $body;

    /**
     * @PHPCRODM\Locale
     */
    protected $locale;

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
     */
    public function getStaticPrefix()
    {
        if (!$this->addLocalePattern) {
            return parent::getStaticPrefix();
        }

        $prefix = $this->getPrefix();
        $path = substr(parent::getPath(), strlen($prefix));
        $path = $prefix.'/{_locale}'.$path;

        return $this->generateStaticPrefix($path, $this->idPrefix);
    }
}
