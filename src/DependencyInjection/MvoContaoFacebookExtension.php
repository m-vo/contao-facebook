<?php

namespace Mvo\ContaoFacebook\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class MvoContaoFacebookExtension extends ConfigurableExtension
{

    /**
     * Configures the passed container according to the merged configuration.
     *
     * @param array            $mergedConfig
     * @param ContainerBuilder $container
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('listener.yml');

        $container->setParameter('mvo_contao_facebook.import_enabled', $mergedConfig['import_enabled']);
        $container->setParameter('mvo_contao_facebook.app_id', $mergedConfig['app_id']);
        $container->setParameter('mvo_contao_facebook.app_secret', $mergedConfig['app_secret']);
        if (isset($mergedConfig['access_token']) && $mergedConfig['access_token'] != '') {
            $container->setParameter('mvo_contao_facebook.access_token', $mergedConfig['access_token']);
        } else {
            // fallback for missing access token
            $container->setParameter(
                'mvo_contao_facebook.access_token',
                $mergedConfig['app_id'] . '|' . $mergedConfig['app_secret']
            );
        }
        $container->setParameter('mvo_contao_facebook.minimum_cache_time', $mergedConfig['minimum_cache_time']);
        $container->setParameter('mvo_contao_facebook.fb_page_name', $mergedConfig['fb_page_name']);
        $container->setParameter('mvo_contao_facebook.number_of_posts', $mergedConfig['number_of_posts']);
    }
}