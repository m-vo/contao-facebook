<?php

declare(strict_types = 1);

namespace Mvo\ContaoFacebook;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;

class ContaoManagerPlugin implements BundlePluginInterface//, RoutingPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(MvoContaoFacebookBundle::class)
                ->setLoadAfter(
                    [
                        //\Symfony\Bundle\TwigBundle\TwigBundle::class,
                        \Contao\CoreBundle\ContaoCoreBundle::class,
                        \Contao\ManagerBundle\ContaoManagerBundle::class,
                        'haste'
                    ]
                ),
        ];
    }

//    /**
//     * Returns a collection of routes for this bundle.
//     *
//     * @param LoaderResolverInterface $resolver
//     * @param KernelInterface         $kernel
//     *
//     * @return null|RouteCollection
//     */
//    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
//    {
//        return $resolver
//            ->resolve(__DIR__ . '/Resources/config/routing.yml')
//            ->load(__DIR__ . '/Resources/config/routing.yml');
//    }
}
