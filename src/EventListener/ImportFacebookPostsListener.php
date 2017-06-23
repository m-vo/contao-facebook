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
use Mvo\ContaoFacebook\Model\FacebookPostModel;

class ImportFacebookPostsListener extends ImportFacebookDataListener
{
    /**
     * Entry point: Import/update facebook events.
     */
    protected function import()
    {
        // find existing posts
        $objPosts       = FacebookPostModel::findAll();
        $postDictionary = [];
        if (null !== $objPosts) {
            foreach ($objPosts as $objPost) {
                /** @var FacebookPostModel $objPost */
                $postDictionary[$objPost->postId] = $objPost;
            }
        }

        // query facebook for current posts
        $numPosts  = $this->container->getParameter('mvo_contao_facebook.number_of_posts');
        $graphEdge = OpenGraph::queryEdge(
            'posts',
            [
                'id',
                'created_time',
                'type',
                'caption',
                'link',
                'message',
                'picture',
                'object_id',
                'updated_time'
            ],
            ['limit' => $numPosts]
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

            // skip if message is empty or type is 'link'
            if('' == $graphNode->getField('message', '') || 'link' == $graphNode->getField('type', '')) {
                continue;
            }

            if (array_key_exists($fbId, $postDictionary)) {
                // update existing item
                if ($this->updateRequired($graphNode, $postDictionary[$fbId])) {
                    $this->updatePost($postDictionary[$fbId], $graphNode);
                }
                unset($postDictionary[$fbId]);

            } else {
                // create new item
                $post = new FacebookPostModel();

                $post->postId = $fbId;
                $this->updatePost($post, $graphNode);
            }
        }

        // remove orphans
        /** @var FacebookPostModel $post */
        foreach ($postDictionary as $post) {
            // todo: generalize with dca's ondelete_callback
            if ($post->image && $file = FilesModel::findByUuid($post->image)) {
                /** @var Collection $objPosts */
                $objPosts = FacebookPostModel::findBy('image', $post->image);
                if ($objPosts->count() == 1) {
                    Files::getInstance()->delete($file->path);
                    Dbafs::deleteResource($file->path);
                }
            }
            $post->delete();
        }
    }

    /**
     * @param GraphNode         $graphNode
     * @param FacebookPostModel $post
     *
     * @return bool
     */
    private function updateRequired(GraphNode $graphNode, FacebookPostModel $post)
    {
        return $this->getTime($graphNode, 'updated_time') != $post->lastChanged;
    }

    /**
     * @param FacebookPostModel $event
     * @param GraphNode         $graphNode
     */
    private function updatePost(FacebookPostModel $event, GraphNode $graphNode)
    {
        $event->tstamp      = time();
        $event->postTime    = $this->getTime($graphNode, 'created_time');
        $event->message     = Tools::encodeText($graphNode->getField('message', ''));
        $event->image       = $this->getImage($graphNode);
        $event->lastChanged = $this->getTime($graphNode, 'updated_time');

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
     * @return null|string
     */
    private function getImage(GraphNode $graphNode)
    {
        if (null !== $graphNode->getField('picture', null)
            && null !== $objectId = $graphNode->getField('object_id', null)
        ) {
            $metaData = [
                'caption' =>
                    [
                        'caption' => $graphNode->getField('caption', ''),
                        'link'    => $graphNode->getField('link', ''),
                    ]
            ];

            $fileModel = ImageScraper::scrape(
                $objectId,
                $graphNode->getField('type', ''),
                serialize($metaData)
            );
            return (null !== $fileModel) ? $fileModel->uuid : null;
        }

        return null;
    }
}