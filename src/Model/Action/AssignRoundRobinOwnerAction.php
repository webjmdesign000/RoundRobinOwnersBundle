<?php

namespace MauticPlugin\RoundRobinOwnersBundle\Model\Action;

use Mautic\CampaignBundle\Model\Action\AbstractAction;
use Mautic\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AssignRoundRobinOwnerAction extends AbstractAction
{
    protected $logger;
    protected $userRepository;
    protected $entityManager;
    protected $container;

    public function __construct(
        $dispatcher,
        $config,
        $translator,
        $logger,
        $userRepository,
        EntityManagerInterface $entityManager,
        ContainerInterface $container
    ) {
        parent::__construct($dispatcher, $config, $translator);
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    public function getName()
    {
        return 'round_robin_owner';
    }

    public function getLabel()
    {
        return 'Round Robin Owners';
    }

    public function getDescription()
    {
        return 'Assigns a contact to an owner in a round-robin fashion and optionally sends an email to the owner.';
    }

    public function getFormOptions()
    {
        $users = $this->userRepository->findBy(['isActive' => 1]);

        $choices = [];
        foreach ($users as $user) {
            $choices[$user->getUsername()] = $user->getId();
        }

        return [
            'owners' => [
                'label'    => 'Select Owners',
                'type'     => 'checkboxes',
                'options'  => $choices,
                'attr'     => ['class' => 'form-control'],
                'required' => true,
            ],
            'send_email' => [
                'label'    => 'Send Email to Owner',
                'type'     => 'checkbox',
                'attr'     => ['class' => 'form-control'],
                'required' => false,
            ],
        ];
    }

    public function execute($object, $args, $event)
    {
        // Retrieve owners from args
        $ownerIds = isset($args['owners']) ? $args['owners'] : [];
        $sendEmail = isset($args['send_email']) ? $args['send_email'] : false;

        if (empty($ownerIds)) {
            $this->logger->error('No owners selected for Round Robin assignment.');
            return false;
        }

        // Retrieve last owner index from cache
        $ownerIndex = $this->getCurrentOwnerIndex(count($ownerIds));

        $selectedOwnerId = $ownerIds[$ownerIndex];
        $owner = $this->userRepository->find($selectedOwnerId);

        if (!$owner) {
            $this->logger->error('Selected owner not found.');
            return false;
        }

        // Assign the owner to the contact
        $contact = $object;
        $contact->setOwner($owner);
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        // Optionally send email to the owner
        if ($sendEmail) {
            $this->sendEmailToOwner($owner, $contact);
        }

        // Update the owner index for next assignment
        $this->incrementOwnerIndex();

        return true;
    }

    private function getCurrentOwnerIndex($totalOwners)
    {
        // Using cache directory
        $cacheDir = $this->container->getParameter('kernel.cache_dir');
        $filePath = $cacheDir . '/round_robin_owner_index.txt';

        if (file_exists($filePath)) {
            $index = (int) file_get_contents($filePath);
        } else {
            $index = 0;
        }

        return $index % $totalOwners;
    }

    private function incrementOwnerIndex()
    {
        $cacheDir = $this->container->getParameter('kernel.cache_dir');
        $filePath = $cacheDir . '/round_robin_owner_index.txt';

        if (file_exists($filePath)) {
            $index = (int) file_get_contents($filePath);
        } else {
            $index = 0;
        }
        $index++;
        file_put_contents($filePath, $index);
    }

    private function sendEmailToOwner(User $owner, $contact)
    {
        // Implement email sending logic using Mautic’s email service
        $emailAlias = 'owner-assignment'; // Ensure this alias exists in your Mautic Emails

        $emailModel = $this->container->get('mautic.email.model.email');
        $email = $emailModel->getRepository()->findOneBy(['isPublished' => 1, 'alias' => $emailAlias]);

        if (!$email) {
            $this->logger->error('No published email with alias "owner-assignment" found.');
            return;
        }

        // Prepare recipient
        $recipient = [$owner->getEmail() => $owner->getUsername()];

        // Send the email
        $emailModel->sendMessage($email, $recipient, $contact);
    }
}
