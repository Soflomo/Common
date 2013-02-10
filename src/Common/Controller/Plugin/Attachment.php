<?php
/**
 * Copyright (c) 2012 Soflomo http://soflomo.com.
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
 * @package     SoflomoCommon
 * @author      Jurian Sluiman <jurian@soflomo.com>
 * @copyright   2012 Soflomo http://soflomo.com.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 */

namespace SoflomoCommon\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use SoflomoCommon\Exception\InvalidArgumentException;

class Attachment extends AbstractPlugin
{
    protected $defaultType        = 'application/octet-stream';
    protected $defaultDisposition = 'attachment';

    public function fromFile($path, $name=null, $type=null, $disposition=null)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(
                sprintf('File at %s does not exist', $path)
            );
        }
        $data = file_get_contents($path);

        if (null === $name) {
            $name = basename($path);
        }

        return $this->createAttachment($data, $name, $type, $disposition);
    }

    public function fromBlob($blob, $name, $type=null, $disposition=null)
    {
        return $this->createAttachment($blob, $name, $type, $disposition);
    }

    public function fromStream()
    {
        throw new NotImplementedException();
    }

    protected function createAttachment($data, $name, $type=null, $disposition=null)
    {
        if (null === $type) {
            $type = $this->defaultType;
        }
        if (null === $disposition) {
            $disposition = $this->defaultDisposition;
        }

        $response = $this->getController()->getResponse();

        $response->setContent($data);
        $response->getHeaders()
                 ->addHeaderLine('Pragma', 'public')
                 ->addHeaderLine('Cache-control', 'must-revalidate, post-check=0, pre-check=0')
                 ->addHeaderLine('Cache-control', 'private')
                 ->addHeaderLine('Expires', '0000-00-00')
                 ->addHeaderLine('Content-Type', $type)
                 ->addHeaderLine('Content-Transfer-Encoding', 'binary')
                 ->addHeaderLine('Content-Length', strlen($data))
                 ->addHeaderLine('Content-Disposition', $disposition . '; filename=' . $name);

        return $response;
    }
}
