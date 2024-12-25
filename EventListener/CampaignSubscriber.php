<?php

namespace RoundRobinOwnersBundle\EventListener;

use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CampaignBundle\CampaignEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mautic\UserBundle\Model\UserModel;

class CampaignSubscriber implements EventSubscriberInterface
{
    private $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD   => ['onCampaignBuild', 0],
            CampaignEvents::CAMPAIGN_ON_TRIGGER => ['onCampaignTrigger', 0],
        ];
    }

    public function onCampaignBuild(CampaignExecutionEvent $event)
    {
        $event->addAction(
            'round_robin_owner',
            [
                'label'            => 'Round Robin Owner',
                'description'      => 'Assigns owners in a round-robin manner.',
                'formType'         => 'roundrobinowner',
                'formTypeOptions'  => [],
                'eventName'        => 'plugin.roundrobinowner.assign',
            ]
        );
    }

    public function onCampaignTrigger(CampaignExecutionEvent $event)
    {
        $contacts = $event->getContacts();
        $config = $event->getConfig();
        $selectedUsers = $config['selected_users'] ?? [];
        $toggleEmail = $config['toggle_email'] ?? false;

        if (empty($selectedUsers)) {
            return;
        }

        $userIndex = 0;
        foreach ($contacts as $contact) {
            $selectedUser = $selectedUsers[$userIndex % count($selectedUsers)];
            $contact->setOwner($this->userModel->getEntity($selectedUser));
            
            if ($toggleEmail) {
                // Add email sending logic if required
            }

            $userIndex++;
        }
    }
}
