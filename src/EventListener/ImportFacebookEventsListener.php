<?php

namespace Mvo\ContaoFacebook\EventListener;

use Contao\Dbafs;
use Contao\Files;
use Contao\FilesModel;
use Contao\Model\Collection;
use Facebook\GraphNodes\GraphNode;
use Mvo\ContaoFacebook\Facebook\Tools;
use Mvo\ContaoFacebook\Facebook\ImageScraper;
use Mvo\ContaoFacebook\Facebook\OpenGraph;
use Mvo\ContaoFacebook\Model\FacebookEventModel;

class ImportFacebookEventsListener extends ImportFacebookDataListener
{
    /**
     * Entry point: Import/update facebook events.
     */
    protected function import()
    {
        // find existing events
        $objEvents       = FacebookEventModel::findAll();
        $eventDictionary = [];
        if (null !== $objEvents) {
            foreach ($objEvents as $objEvent) {
                /** @var FacebookEventModel $objEvent */
                $eventDictionary[$objEvent->eventId] = $objEvent;
            }
        }

        // query facebook for upcoming events
        $searchSince = strtotime('today midnight');
        $graphEdge   = OpenGraph::queryEdge(
            'events',
            [
                'id',
                'name',
                'description',
                'start_time',
                'place',
                'cover',
                'ticket_uri',
                'updated_time'
            ],
            ['since' => $searchSince]
        );
        if (null == $graphEdge) {
            return;
        }

        // merge the data
        /** @var GraphNode $graphNode */
        foreach ($graphEdge as $graphNode) {
            $fbId = $graphNode->getField('id', null);
            if ($fbId === null) {
                continue;
            }

            if (array_key_exists($fbId, $eventDictionary)) {
                // update existing item
                if ($this->updateRequired($graphNode, $eventDictionary[$fbId])) {
                    $this->updateEvent($eventDictionary[$fbId], $graphNode);
                }
                unset($eventDictionary[$fbId]);

            } else {
                // create new item
                $event = new FacebookEventModel();

                $event->eventId = $fbId;
                $this->updateEvent($event, $graphNode);
            }
        }

        // remove orphans
        /** @var FacebookEventModel $post */
        foreach ($eventDictionary as $event) {
            // todo: generalize with dca's ondelete_callback
            if ($event->image && $file = FilesModel::findByUuid($event->image)) {
                /** @var Collection $objEvents */
                $objEvents = FacebookEventModel::findBy('image', $event->image);
                if ($objEvents->count() == 1) {
                    Files::getInstance()->delete($file->path);
                    Dbafs::deleteResource($file->path);
                }
            }
            $event->delete();
        }
    }

    /**
     * @param GraphNode          $graphNode
     * @param FacebookEventModel $event
     *
     * @return bool
     */
    private function updateRequired(GraphNode $graphNode, FacebookEventModel $event)
    {
        return $this->getTime($graphNode, 'updated_time') != $event->lastChanged;
    }

    /**
     * @param FacebookEventModel $event
     * @param GraphNode          $graphNode
     */
    private function updateEvent(FacebookEventModel $event, GraphNode $graphNode)
    {
        $event->tstamp       = time();
        $event->name         = Tools::encodeText($graphNode->getField('name', ''));
        $event->description  = Tools::encodeText($graphNode->getField('description', ''));
        $event->startTime    = $this->getTime($graphNode, 'start_time');
        $event->locationName = Tools::encodeText($this->getLocationName($graphNode));
        $event->image        = $this->getImage($graphNode);
        $event->ticketUri    = $graphNode->getField('ticket_uri', '');
        $event->lastChanged  = $this->getTime($graphNode, 'updated_time');

        $event->save();
    }

    /**
     * @param GraphNode $graphNode
     * @param string    $field
     *
     * @return int
     */
    private function getTime(GraphNode $graphNode, string $field)
    {
        /** @var \DateTime $date */
        $date = $graphNode->getField($field, null);
        return ($date !== null) ? $date->getTimestamp() : 0;
    }

    /**
     * @param GraphNode $graphNode
     *
     * @return string
     */
    private function getLocationName(GraphNode $graphNode)
    {
        /** @var GraphNode $place */
        $place = $graphNode->getField('place', null);
        return ($place !== null) ? $place->getField('name', '') : '';
    }

    /**
     * @param GraphNode $graphNode
     *
     * @return null|string
     */
    private function getImage(GraphNode $graphNode)
    {
        if (null != ($cover = $graphNode->getField('cover', null))
            && null != ($objectId = $cover->getField('id', null))
        ) {
            $metaData = [
                'caption' =>
                    [
                        'caption' => $graphNode->getField('name', ''),
                        'link'    => sprintf('https://facebook.com/%s', $graphNode->getField('id', ''))
                    ]
            ];

            $fileModel = ImageScraper::scrape(
                $objectId,
                'photo',
                serialize($metaData)
            );
            return (null !== $fileModel) ? $fileModel->uuid : null;
        }

        return null;
    }
}