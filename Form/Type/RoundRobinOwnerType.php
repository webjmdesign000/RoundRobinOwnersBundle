<?php

namespace RoundRobinOwnersBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Mautic\UserBundle\Model\UserModel;

class RoundRobinOwnerType extends AbstractType
{
    private $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'selected_users',
            ChoiceType::class,
            [
                'label'    => 'Select Owners',
                'choices'  => $this->getUsers(),
                'multiple' => true,
                'expanded' => true,
            ]
        );

        $builder->add(
            'toggle_email',
            CheckboxType::class,
            [
                'label'    => 'Send email to owners?',
                'required' => false,
            ]
        );
    }

    private function getUsers()
    {
        $users = $this->userModel->getEntities();
        $choices = [];

        foreach ($users as $user) {
            $choices[$user->getName()] = $user->getId();
        }

        return $choices;
    }
}
