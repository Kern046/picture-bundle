<?php

namespace Kern\PictureBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('kern_picture');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('upload_dir')->end()
                ->scalarNode('user_class')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}