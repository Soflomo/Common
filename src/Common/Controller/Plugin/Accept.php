<?php

use Zend\Http\Request;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\MvcEvent;

class Accept extends AbstractPlugin
{
    public function match($mimeType)
    {
        if (is_string($mimeType)) {
            $mimeType = (array) $mimeType;
        } elseif (!is_array($mimeType)) {
            throw new InvalidArgumentException(
                sprintf('Invalid mime type given, string or array accepted, %s given',
                        gettype($mimeType)
                )
            );
        }

        $headers = $this->getRequest()->getHeaders();
        if (!$headers->has('Accept')) {
            return false;
        }

        $accept  = $headers->get('Accept');
        $against = $this->createMatchedAgainstString($mimeType);
        return (bool) $accept->match($against);
    }

    /**
     * Get the request
     *
     * @return Request
     * @throws DomainException if unable to find request
     */
    protected function getRequest()
    {
        if ($this->request) {
            return $this->request;
        }

        $event = $this->getEvent();
        $request = $event->getRequest();
        if (!$request instanceof Request) {
            throw new DomainException(
                    'The event used does not contain a valid Request, but must.'
            );
        }

        $this->request = $request;
        return $request;
    }

    /**
     * Get the event
     *
     * @return MvcEvent
     * @throws DomainException if unable to find event
     */
    protected function getEvent()
    {
        if ($this->event) {
            return $this->event;
        }

        $controller = $this->getController();
        if (!$controller instanceof InjectApplicationEventInterface) {
            throw new DomainException(
                    'A controller that implements InjectApplicationEventInterface '
                  . 'is required to use ' . __CLASS__
            );
        }

        $event = $controller->getEvent();
        if (!$event instanceof MvcEvent) {
            $params = $event->getParams();
            $event = new MvcEvent();
            $event->setParams($params);
        }
        $this->event = $event;

        return $this->event;
    }
}
