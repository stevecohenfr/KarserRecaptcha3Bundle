<?php

namespace Karser\Recaptcha3Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        // BC layer for symfony/config 4.1 and older
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('karser_recaptcha3', 'array');
        $rootNode
            ->children()
                ->scalarNode('site_key')->isRequired()->end()
                ->scalarNode('secret_key')->isRequired()->end()
                ->floatNode('score_threshold')->min(0.0)->max(1.0)->defaultValue(0.5)->end()
                ->booleanNode('enabled')->defaultTrue()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
