<?php
// File: Action/RoundRobinOwnerAction.php

namespace MauticPlugin\RoundRobinOwnerBundle\Action;

use Mautic\CampaignBundle\Action\AbstractAction;
use Mautic\CampaignBundle\Model\ActionModel;
use Mautic\CoreBundle\Helper\SluggerHelper;
use Mautic\UserBundle\Model\UserModel;
use Mautic\EmailBundle\Mailer\Mailer;
use Mautic\EmailBundle\Entity\Email;
use Mautic\CoreBundle\Factory\MauticFactory;

class RoundRobinOwnerAction extends AbstractAction
{
    protected $mailer;
    protected $userModel;
    protected $factory;

    public function __construct(
        MauticFactory $factory,
        SluggerHelper $sluggerHelper,
        $emailHelper,
        $entityManager
    ) {
        parent::__construct($factory, $sluggerHelper, $emailHelper, $entityManager);
    }

    public function getName()
    {
        return 'round_robin_owner';
    }

    public function getLabel()
    {
        return 'Round Robin Owner';
    }

    public function getDescription()
    {
        return 'Assigns a contact to a selected owner in a round-robin fashion and optionally sends an email notification.';
    }

    public function getCategory()
    {
        return 'Contact Management';
    }

    public function execute($action, $lead, $campaign)
    {
        // Fetch selected owners and email toggle from action parameters
        $params = $action->getSettings();

        if (!isset($params['owners']) || empty($params['owners'])) {
            return false;
        }

        $owners = $params['owners'];
        $sendEmail = isset($params['send_email']) ? $params['send_email'] : false;

        // Fetch the next owner in round-robin
        $ownerId = $this->getNextOwner($owners);
        if (!$ownerId) {
            return false;
        }

        // Assign the owner to the contact
        $lead->setOwner($ownerId);
        $this->getModel('lead')->saveEntity($lead);

        // Optionally send an email to the owner
        if ($sendEmail) {
            $this->sendEmailToOwner($ownerId, $lead);
        }

        return true;
    }

    private function getNextOwner($owners)
    {
        // Implement round-robin logic. For simplicity, store last owner in cache or database
        // Here, we'll use a file-based approach for demonstration

        $cacheFile = __DIR__ . '/../../cache/round_robin_owner.cache';
        if (file_exists($cacheFile)) {
            $lastIndex = (int)file_get_contents($cacheFile);
        } else {
            $lastIndex = -1;
        }

        $nextIndex = ($lastIndex + 1) % count($owners);
        file_put_contents($cacheFile, $nextIndex);

        return $owners[$nextIndex];
    }

    private function sendEmailToOwner($ownerId, $lead)
    {
        $user = $this->factory->getModel('user')->getEntity($ownerId);
        if (!$user || !$user->getEmail()) {
            return;
        }

        // Create and send the email
        $email = new Email();
        $email->setSubject('New Contact Assigned: ' . $lead->getName());
        $email->setHtml('<p>You have been assigned a new contact: ' . $lead->getName() . '</p>');
        $email->setFromName('Mautic');
        $email->setFromEmail('no-reply@example.com');
        $email->addTo($user->getEmail(), $user->getName());

        $mailer = $this->factory->getMailer();
        $mailer->sendEmail($email);
    }

    public function buildActionForm($builder, $options)
    {
        $users = $this->factory->getModel('user')->getEntities(['isActive' => 1]);

        $choices = [];
        foreach ($users as $user) {
            $choices[$user->getUsername()] = $user->getId();
        }

        $builder->add('owners', 'choice', [
            'label' => 'Select Owners',
            'choices' => $choices,
            'multiple' => true,
            'expanded' => true,
            'required' => true,
        ]);

        $builder->add('send_email', 'checkbox', [
            'label' => 'Send Email Notification',
            'required' => false,
        ]);
    }
}
