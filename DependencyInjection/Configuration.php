<?php
/**
 * @package EncreInformatique\UserApiBundle\DependencyInjection
 * User: jdevergnies
 * Date: 2019-03-31
 * Time: 10:32
 */

namespace EncreInformatique\UserApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('user_api_bundle');

        $rootNode
            ->children()
            ->arrayNode('entities')
            //->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('user')
            //->defaultValue('\TLH\URLCheckerBundle\Entity\User')
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
