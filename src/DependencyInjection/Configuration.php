<?php

namespace Mvo\ContaoFacebook\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('mvo_contao_facebook');

        $rootNode->children()
            ->scalarNode('app_id')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('app_secret')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('access_token')
            ->end()
            ->integerNode('minimum_cache_time')
                ->defaultValue(1000)
                ->min(0)
            ->end()
            ->scalarNode('fb_page_name')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->integerNode('number_of_posts')
                ->defaultValue(5)
                ->min(1)
            ->end();

        return $treeBuilder;
    }
}