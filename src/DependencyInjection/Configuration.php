<?php

declare(strict_types=1);

namespace VC4SM\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('vc4sm');

        $treeBuilder->getRootNode()
            ->children()
                        ->scalarNode('aries_agent_university')->end()
                        ->scalarNode('aries_agent_university2')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
