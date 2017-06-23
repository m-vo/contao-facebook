<?php

namespace Mvo\ContaoFacebook\Element;


use Contao\BackendTemplate;
use Contao\Config;
use Contao\ContentElement;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Model\Collection;
use Mvo\ContaoFacebook\Facebook\Tools;
use Mvo\ContaoFacebook\Model\FacebookEventModel;

class ContentEventList extends ContentElement
{
    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_mvo_facebook_event_list';

    /**
     * Parse the template
     *
     * @return string Parsed element
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate        = new BackendTemplate('be_wildcard');
            $objTemplate->title = 'Facebook Events';
            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Compile the content element
     *
     * @return void
     */
    protected function compile()
    {
        $this->Template = new FrontendTemplate($this->strTemplate);
        $this->Template->setData($this->arrData);


        /** @var Collection|FacebookEventModel $objEvents */
        $objEvents = FacebookEventModel::findBy('visible', true, ['order' => 'startTime']);

        $arrEvents = [];
        if (null != $objEvents) {
            $i     = 0;
            $total = $objEvents->count();

            /** @var FacebookEventModel $event */
            foreach ($objEvents as $event) {
                // base data
                $arrEvent = [
                    'eventId'      => $event->eventId,
                    'name'         => Tools::formatText($event->name),
                    'description'  => Tools::formatText($event->description),
                    'locationName' => Tools::formatText($event->locationName),
                    'time'         => $event->startTime,
                    'datetime'     => date(Config::get('datimFormat'), $event->startTime),
                    'href'         => sprintf('https://facebook.com/%s', $event->eventId),
                ];

                // css enumeration
                $arrEvent['class'] = ((1 == $i % 2) ? ' even' : ' odd') .
                                     ((0 == $i) ? ' first' : '') .
                                     (($total - 1 == $i) ? ' last' : '');
                $i++;

                // image
                if (null != $event->image
                    && null != $objFile = FilesModel::findByUuid($event->image)
                ) {
                    $objImageTemplate = new FrontendTemplate('image');

                    $arrMeta = deserialize($objFile->meta, true);
                    $strAlt  = (array_key_exists('caption', $arrMeta)
                                && is_array($arrMeta['caption'])
                                && array_key_exists('caption', $arrMeta['caption']))
                               && '' != $arrMeta['caption']['caption']
                        ? $arrMeta['caption']['caption'] : 'Facebook Post Image';

                    $this->addImageToTemplate(
                        $objImageTemplate,
                        [
                            'singleSRC' => $objFile->path,
                            'alt'       => $strAlt,
                            'size'      => deserialize($this->size),
                        ]
                    );
                    $arrEvent['image']    = $objImageTemplate->parse();
                    $arrEvent['hasImage'] = true;
                } else {
                    $arrEvent['hasImage'] = false;
                }

                $arrEvents[] = $arrEvent;
            }
        }

        $this->Template->events    = $arrEvents;
        $this->Template->hasEvents = 0 != count($arrEvents);
    }
}