<?php
// File: Subscriber/RoundRobinOwnerSubscriber.php

namespace MauticPlugin\RoundRobinOwnerBundle\Subscriber;

use Mautic\CampaignBundle\EventBuilder\ActionBuilderEvent;
use Mautic\PluginBundle\Event\PluginEvents;
use Mautic\PluginBundle\Event\PluginEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use MauticPlugin\RoundRobinOwnerBundle\Action\RoundRobinOwnerAction;

class RoundRobinOwnerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::ACTION_BUILD => ['onActionBuild', 0],
        ];
    }

    public function onActionBuild(ActionBuilderEvent $event)
    {
        $event->addAction(new RoundRobinOwnerAction(
            $this->factory,
            $this->sluggerHelper,
            $this->emailHelper,
            $this->entityManager
        ));
    }
}
