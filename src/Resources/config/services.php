<?php

use MauticPlugin\RoundRobinOwnersBundle\Model\Action\AssignRoundRobinOwnerAction;

return [
    'services' => [
        'mautic.roundrobinowners.action.assign_round_robin_owner' => [
            'class'     => AssignRoundRobinOwnerAction::class,
            'arguments' => [
                'event_dispatcher',
                'mautic.helper.core_parameters',
                'translator',
                'logger',
                'mautic.user.repository.user',
                'doctrine.orm.entity_manager',
                'service_container', // Necessary for accessing the container
            ],
            'tags'      => [
                ['name' => 'mautic.action'],
            ],
        ],
    ],
];
