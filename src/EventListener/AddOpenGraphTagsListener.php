<?php

namespace Mvo\ContaoFacebook\EventListener;

use Contao\File;
use Contao\FilesModel;
use Contao\PageModel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AddOpenGraphTagsListener implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param PageModel $objPage
     */
    public function onInject(PageModel $objPage)
    {
        $objRootPage = PageModel::findById($objPage->rootId);

        if (null != $objRootPage && $objRootPage->mvo_facebook_og_enabled) {
            $GLOBALS['TL_HEAD'][] = self::generateMetaTags($objPage);
        }
    }

    /**
     * @param PageModel $objPage
     *
     * @return string
     */
    private function generateMetaTags(PageModel $objPage): string
    {
        $arrData = [
            'type'   => 'website',
            'url'    => $objPage->getAbsoluteUrl(),
            'title'  => $objPage->title,
            'images' => self::getImageAttributes($objPage),
            // todo: check if there is a way to get the correct locale: 'language_territory'
            'locale' => $objPage->language
        ];

        if ('' != $objPage->description) {
            $arrData['description'] = $objPage->description;
        }
        if ('' != $objPage->rootTitle) {
            $arrData['site_name'] = $objPage->rootTitle;
        }

        return $this->container->get('twig')->render('@MvoContaoFacebook/meta_tags.html.twig', $arrData);
    }

    /**
     * @param PageModel $objPage
     *
     * @return array
     */
    private static function getImageAttributes(PageModel $objPage): array
    {
        // find closest image(s) in tree
        $images = null;
        do {
            if (null != $objPage->mvo_facebook_og_images_order && '' != $objPage->mvo_facebook_og_images_order) {
                $images = deserialize($objPage->mvo_facebook_og_images);
            } elseif (null != $objPage->mvo_facebook_og_images && '' != $objPage->mvo_facebook_og_images) {
                $images = deserialize($objPage->mvo_facebook_og_images);
            }
        } while (null == $images && null != $objPage = PageModel::findById($objPage->pid));

        if (null == $images) {
            return [];
        }

        // get attributes
        $arrImageAttributes = [];
        foreach ($images as $imageUuid) {
            $imageAttributes = self::getImageAttribute($imageUuid);
            if (null != $imageAttributes) {
                $arrImageAttributes[] = $imageAttributes;
            }
        }

        return $arrImageAttributes;
    }

    /**
     * @param string $imageUuid
     *
     * @return array|null
     */
    private static function getImageAttribute(string $imageUuid)
    {
        $objFilesModel = FilesModel::findByUuid($imageUuid);

        if (null != $objFilesModel && null != $objFile = new File($objFilesModel->path)
        ) {
            $arrAttributes = $objFile->imageSize;
            return [
                'src'    => $objFilesModel->path,
                'width'  => $arrAttributes[0],
                'height' => $arrAttributes[1],
                'mime'   => $arrAttributes['mime']
            ];
        }

        return null;
    }
}