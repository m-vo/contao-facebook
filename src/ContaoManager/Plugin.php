<?php

declare(strict_types = 1);

namespace Mvo\ContaoFacebook\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Dependency\DependentPluginInterface;
use Mvo\ContaoFacebook\MvoContaoFacebookBundle;

class Plugin implements BundlePluginInterface, DependentPluginInterface
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
                        \Symfony\Bundle\TwigBundle\TwigBundle::class,
                        \Contao\CoreBundle\ContaoCoreBundle::class,
                        \Contao\ManagerBundle\ContaoManagerBundle::class,
                        'haste'
                    ]
                ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getPackageDependencies()
    {
        return [
            'facebook/graph-sdk',
            'guzzlehttp/guzzle',
        ];
    }

}
