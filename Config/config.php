<?php

return [
    'name'        => 'RoundRobinOwner',
    'description' => 'A custom campaign action for Round Robin owner assignment.',
    'version'     => '1.0',
    'author'      => 'WebjmDesign000',
    'services'    => [
        'events' => [
            'your_plugin.campaign.subscriber' => [
                'class'     => 'RoundRobinOwnersBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.helper.integration',
                    'mautic.lead.model.lead',
                    'mautic.user.model.user',
                ],
            ],
        ],
        'forms' => [
            'your_plugin.form.type.roundrobinowner' => [
                'class'     => 'RoundRobinOwnersBundle\Form\Type\RoundRobinOwnerType',
                'arguments' => [],
                'alias'     => 'roundrobinowner',
            ],
        ],
    ],
];
