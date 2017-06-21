<?php

namespace Mvo\ContaoFacebook\EventListener;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Mvo\ContaoFacebook\Model\FacebookEventModel;
use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class ImportFacebookDataListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param ContainerInterface       $container
     */
    public function __construct(ContaoFrameworkInterface $framework, ContainerInterface $container)
    {
        $this->framework = $framework;
        $this->container = $container;
    }

    /**
     * Trigger import.
     */
    public function onImport()
    {
        $this->framework->initialize();

        if ($this->shouldReImport()) {
            $this->import();
        }
    }

    /**
     * @return bool Returns true if the present data exceeds the minimum cache time.
     */
    private function shouldReImport()
    {
        $diff             = time() - FacebookEventModel::getLastTimestamp();
        $minimumCacheTime = $this->container->getParameter('mvo_contao_facebook.minimum_cache_time');

        return $diff >= $minimumCacheTime;
    }

    protected abstract function import();

}