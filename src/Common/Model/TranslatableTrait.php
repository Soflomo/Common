<?php
/**
 * Copyright (c) 2014 Soflomo.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2014 Soflomo.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 */

namespace Soflomo\Common\Model;

use Locale;
use InvalidArgumentException;
use Doctrine\Common\Collections\ArrayCollection;

trait TranslatableTrait
{
    /**
     * List of translations
     *
     * @var ArrayCollection
     */
    protected $translations;

    /**
     * Flag to indicate re-indexed translations
     * @var bool
     */
    protected $indexed = false;

    /**
     * Locale currently in use
     *
     * @var string
     */
    protected $locale;

    public function __construct()
    {
        $this->translations = new ArrayCollection;
    }

    /**
     * Getter for locale
     *
     * @return string
     */
    public function getLocale()
    {
        if (null === $this->locale) {
            $this->locale = $this->detectLocale();
        }

        return $this->locale;
    }

    /**
     * Setter for locale
     *
     * @param  string $locale Value to set
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Find locale based on default set locale
     *
     * @return string
     */
    protected function detectLocale()
    {
        return Locale::getDefault();
    }

    /**
     * Get translation object for given locale
     *
     * When no locale is set, use the default locale
     *
     * @param string|null $locale Locale to use
     * @return TranslationInterface
     */
    public function getTranslation($locale = null)
    {
        if (false === $this->indexed) {
            $this->indexTranslations();
        }

        $locale = $locale ?: $this->getLocale();
        if ($this->translations->containsKey($locale)) {
            return $this->translations->get($locale);
        }
    }

    /**
     * Set new translation object for this entity
     *
     * @param TranslationInterface $translation
     */
    public function setTranslation(TranslationInterface $translation)
    {
        $locale = $translation->getLocale();
        $this->translations->set($locale, $translation);
    }

    /**
     * Reindex translation collection
     *
     * Create a new collection to use the locale as key, for direct
     * array access of the translation objects
     *
     * @return void
     */
    protected function indexTranslations()
    {
        // Don't index twice
        if (true === $this->indexed) {
            return;
        }

        $translationCopy = $this->translations->toArray();

        $translations = new ArrayCollection;
        foreach ($translationCopy as $translation) {
            $locale = $translation->getLocale();
            $translations->set($locale, $translation);
        }

        $this->translations = $translations;
        $this->indexed      = true;
    }

    /**
     * Create new translation object
     *
     * @return TranslationInterface
     */
    protected function createTranslation()
    {
        $locale = $this->getLocale();
        if ($this->translations->containsKey($locale)) {
            return $this->translations->get($locale);
        }

        if (!isset($this->translationClassName)) {
            throw new InvalidArgumentException(
                'Translation class name is required to create new translation instances'
            );
        }

        $translation = new $this->translationClassName;
        $translation->setObject($this);
        $translation->setLocale($locale);

        $this->setTranslation($translation);
        return $translation;
    }

    /**
     * Proxy method to get property from translation
     *
     * @return mixed
     */
    protected function proxyTranslationGet($property)
    {
        $translation = $this->getTranslation();

        if (!$translation instanceof TranslationInterface) {
            return;
        }

        $method = 'get' . ucfirst($property);
        return $translation->$method();
    }

    /**
     * Proxy method to set property in translation
     *
     * @param string $property Property name
     * @param mixed  $value    Property value
     * @return mixed
     */
    protected function proxyTranslationSet($property, $value)
    {
        $translation = $this->getTranslation();

        if (!$translation instanceof TranslationInterface) {
            $translation = $this->createTranslation();
        }

        $method = 'set' . ucfirst($property);
        return $translation->$method($value);
    }
}