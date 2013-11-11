<?php
/**
 * Copyright (c) 2013 Jurian Sluiman.
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
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 */

namespace Soflomo\Common\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\I18n\Translator\Translator;

class SubMenu extends AbstractHelper
{
    protected $level = 0;
    protected $class;
    protected $header;
    protected $translator;
    protected $textdomain;

    public function __invoke()
    {
        return $this;
    }

    public function setLevel($level)
    {
        $this->level = (int) $level;
        return $this;
    }

    public function setClass($class)
    {
        $this->class = (string) $class;
        return $this;
    }

    public function setHeader($header)
    {
        $this->header = (string) $header;
        return $this;
    }

    public function setTranslator(Translator $translator, $textdomain = 'default')
    {
        $this->translator = $translator;
        $this->textdomain = $textdomain;
        return $this;
    }

    public function __toString()
    {
        $view = $this->getView();
        $menu = $view->navigation()->menu();

        $current = $menu->getContainer();
        $flag    = $menu->getRenderInvisible();
        $active  = $menu->setRenderInvisible(true)->findActive($current);
        $menu->setRenderInvisible($flag);

        if (!$active) {
            return '';
        }

        $container = $active['page'];
        $depth     = $active['depth'];

        while ($this->level <= $depth) {
            $container = $container->getParent();
            $depth--;
        }

        // Set container explicitly to visible
        $flag = $container->isVisible();
        $container->setVisible(true);

        // Inject translator
        $enabled    = $menu->isTranslatorEnabled();
        $translator = $menu->getTranslator();
        $textdomain = $menu->getTranslatorTextDomain();
        if ($this->translator) {
            $menu->setTranslator($this->translator, $this->textdomain);
            $menu->setTranslatorEnabled(true);
        }

        $html = $menu->setContainer($container)
                     ->setUlClass('')
                     ->setOnlyActiveBranch(false)
                     ->setMinDepth(null)
                     ->setMaxDepth(null)
                     ->render();

        // Reset the visibility flag
        $container->setVisible($flag);
        // Reset the container
        $menu->setContainer($current);
        // Reset translator
        $menu->setTranslatorEnabled($enabled);
        $menu->setTranslator($translator, $textdomain);

        if (!strlen($html)) {
            return '';
        }

        $label = $this->header ?: $container->getLabel();
        if ($this->translator) {
            $label = $this->translator->translate($label, $this->textdomain);
        }

        return sprintf('<ul%s><li%s><a href="%s">%s</a>%s</li></ul>',
                (null !== $this->class) ? ' class="' . $this->class . '"' : null,
                ($container->isActive())? ' class="active"' : null,
                $container->getHref(),
                $label,
                $html);
    }
}
