<?php
/**
 * @package UserApiBundle\DependencyInjection
 * User: jdevergnies
 * Date: 2019-03-31
 * Time: 10:27
 */

namespace EncreInformatique\UserApiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class UserApiBundleExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['entities'] as $entity => $entityClass) {
            $container->setParameter('user_api_bundle.entities.'.$entity, $entityClass);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
