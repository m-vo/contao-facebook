<?php

namespace Mvo\ContaoFacebook\EventListener;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DC_Table;
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
     * Trigger import without checking cache time.
     *
     * @param $caller
     */
    public function onForceImport($caller)
    {
        $this->framework->initialize();
        $this->import();

        // if called from within a dca (e.g. global operation) redirect afterwards
        if ($caller instanceof DC_Table) {
            Controller::redirect(Controller::addToUrl(null, true, ['key']));
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