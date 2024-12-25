<?php

namespace MauticPlugin\RoundRobinOwnersBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Mautic\PluginBundle\DependencyInjection\Extension\PluginExtension;

class RoundRobinOwnersExtension extends PluginExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Load services configuration
        $this->initialize($container, $configs);
        $loader = $this->getResolver($container);
        $loader->load('services.php');
    }
}
