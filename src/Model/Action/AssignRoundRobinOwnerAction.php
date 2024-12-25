<?php

namespace MauticPlugin\RoundRobinOwnersBundle\Model\Action;

use Mautic\CampaignBundle\Model\Action\AbstractAction;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CoreBundle\Helper\DateTimeHelper;
use Mautic\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Mautic\EmailBundle\Model\EmailModel;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class AssignRoundRobinOwnerAction extends AbstractAction
{
    protected $logger;
    protected $userRepository;
    protected $entityManager;
    protected $emailModel;
    protected $mailer;

    public function __construct(
        $dispatcher,
        $config,
        $translator,
        LoggerInterface $logger,
        $userRepository,
        EntityManagerInterface $entityManager,
        EmailModel $emailModel,
        MailerInterface $mailer
    ) {
        parent::__construct($dispatcher, $config, $translator);
        $this->logger = $logger;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->emailModel = $emailModel;
        $this->mailer = $mailer;
    }

    public function getName()
    {
        return 'round_robin_owner';
    }

    public function getLabel()
    {
        return $this->translator->trans('round_robin_owner.label', [], 'plugins');
    }

    public function getDescription()
    {
        return $this->translator->trans('round_robin_owner.description', [], 'plugins');
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

    public function execute($object, $args, CampaignExecutionEvent $event)
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

    private function getCurrentOwnerIndex(int $totalOwners): int
    {
        // Using cache directory
        $cacheDir = $this->config['cache_dir'] ?? sys_get_temp_dir();
        $filePath = $cacheDir . '/round_robin_owner_index.txt';

        if (file_exists($filePath)) {
            $index = (int) file_get_contents($filePath);
        } else {
            $index = 0;
        }

        return $index % $totalOwners;
    }

    private function incrementOwnerIndex(): void
    {
        $cacheDir = $this->config['cache_dir'] ?? sys_get_temp_dir();
        $filePath = $cacheDir . '/round_robin_owner_index.txt';

        if (file_exists($filePath)) {
            $index = (int) file_get_contents($filePath);
        } else {
            $index = 0;
        }
        $index++;
        file_put_contents($filePath, $index);
    }

    private function sendEmailToOwner(User $owner, $contact): void
    {
        // Ensure you have an email template with the alias 'owner-assignment' created and published
        $emailAlias = 'owner-assignment';

        $email = $this->emailModel->getRepository()->findOneBy(['isPublished' => 1, 'alias' => $emailAlias]);

        if (!$email) {
            $this->logger->error('No published email with alias "owner-assignment" found.');
            return;
        }

        // Create the email message
        $message = (new Email())
            ->from($email->getFrom())
            ->to($owner->getEmail())
            ->subject($email->getSubject())
            ->html($email->getBody());

        // Send the email
        try {
            $this->mailer->send($message);
            $this->logger->info('Assignment email sent to owner: ' . $owner->getEmail());
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email to owner: ' . $e->getMessage());
        }
    }
}
