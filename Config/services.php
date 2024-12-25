<?php
// File: Config/services.php

use Mautic\CampaignBundle\Model\CampaignModel;
use MauticPlugin\RoundRobinOwnerBundle\Action\RoundRobinOwnerAction;

return [
    'services' => [
        'mautic.plugin.roundrobinowner.action.roundrobinowner' => [
            'class' => RoundRobinOwnerAction::class,
            'arguments' => [
                'mautic.factory',
                'mautic.helper.core_parameters',
                'mautic.helper.email_helper',
                'doctrine.orm.entity_manager',
            ],
            'tag' => 'mautic.action',
        ],
    ],
];
