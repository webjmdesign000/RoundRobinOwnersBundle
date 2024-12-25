<?php
// File: Form/RoundRobinOwnerFormType.php

namespace MauticPlugin\RoundRobinOwnerBundle\Form;

use Mautic\CampaignBundle\Form\Type\ActionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoundRobinOwnerFormType extends ActionType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('owners', 'entity', [
            'class' => 'MauticUserBundle:User',
            'choice_label' => 'username',
            'multiple' => true,
            'expanded' => true,
            'label' => 'Select Owners',
            'required' => true,
        ]);

        $builder->add('send_email', 'checkbox', [
            'label' => 'Send Email Notification',
            'required' => false,
        ]);
    }

    public function getName()
    {
        return 'round_robin_owner';
    }

    public function getFormType()
    {
        return 'round_robin_owner';
    }
}
