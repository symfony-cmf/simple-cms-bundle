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
        $this->setDefault('_locale', $locale);
        $this->locale = $locale;
    }

    public function getPath()
    {
        $prefix = $this->getPrefix();
        $path = substr(parent::getPath(), strlen($prefix));
        return $prefix.'/{_locale}'.$path;
    }
}
