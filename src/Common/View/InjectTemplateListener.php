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
 * @package     Soflomo\Common
 * @subpackage  View\Helper
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2013 Jurian Sluiman.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://soflomo.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Soflomo\Common\View;

use Zend\Mvc\View\Http\InjectTemplateListener as BaseListener;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\Filter;
use Zend\Mvc\MvcEvent;

class InjectTemplateListener extends BaseListener
{
    protected $level;

    public function __construct($level = 2)
    {
        $this->level = $level;
    }

    /**
     * {@inheritdoc}
     */
    public function injectTemplate(MvcEvent $e)
    {
        parent::injectTemplate($e);
    }

    /**
     * {@inheritdoc}
     */
    protected function inflectName($name)
    {
        /**
         * Use a filter chain, to make namespace char also dash separated
         */
        if (!$this->inflector) {
            $this->inflector = new Filter\FilterChain;
            $this->inflector->attach(new Filter\Word\CamelCaseToDash())
                            ->attach(new Filter\Word\SeparatorToDash('\\'));
        }
        $name = $this->inflector->filter($name);
        return strtolower($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function deriveModuleNamespace($controller)
    {
        if (!strstr($controller, '\\')) {
            return '';
        }

        // Get the second namespace separator
        $level  = $this->level - 1;
        $pos    = strpos($controller, '\\', strpos($controller, '\\') + $level);
        $module = substr($controller, 0, $pos);
        return $module;
    }
}
