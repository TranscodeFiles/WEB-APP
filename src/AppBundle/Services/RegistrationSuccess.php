<?php

namespace AppBundle\Services;

use Common\CephBundle\Services\Manager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegistrationSuccess implements EventSubscriberInterface
{
    /**
     * @var Manager $cephManagerService
     */
    private $cephManagerService;

    /**
     * RegistrationSuccess constructor.
     * @param Manager $cephManagerService
     */
    public function __construct(Manager $cephManagerService)
    {
        $this->cephManagerService = $cephManagerService;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationSuccess',
        );
    }

    public function onRegistrationSuccess(FilterUserResponseEvent $event)
    {
        $this->cephManagerService->connection()->putContainer(strval("user" . $event->getUser()->getId()));
    }
}