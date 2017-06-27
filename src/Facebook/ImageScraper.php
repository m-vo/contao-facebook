<?php

namespace Mvo\ContaoFacebook\Facebook;

use Contao\Config;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Dbafs;
use Contao\FilesModel;
use Contao\System;
use Facebook\GraphNodes\GraphNode;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LogLevel;
use Symfony\Component\Config\Definition\Exception\Exception;

class ImageScraper
{
    // todo: consider adding to configuration
    const facebookUploadSubDir = "facebook";

    /**
     * @param string $objectId
     * @param string $type
     * @param string $serializedMetaData
     *
     * @return FilesModel|null
     */
    public static function scrapeObject(string $objectId, string $type, $serializedMetaData = '')
    {
        $destinationFile = self::getUploadDestination($objectId);
        if (null != $fileModel = self::updateResourceIfExists($destinationFile, $objectId, $serializedMetaData)) {
            return $fileModel;
        };

        // try to get best fitting image uri
        $sourceUri = self::getSourceUri($objectId, $type);
        if (null == $sourceUri) {
            return null;
        }

        return self::scrape($sourceUri, $destinationFile, $objectId, $serializedMetaData);
    }

    /**
     * @param string $identifier
     * @param string $sourceUri
     * @param string $serializedMetaData
     *
     * @return FilesModel|null
     */
    public static function scrapeFile(string $identifier, string $sourceUri, $serializedMetaData = '')
    {
        $destinationFile = self::getUploadDestination($identifier);
        if (null != $fileModel = self::updateResourceIfExists($destinationFile, $identifier, $serializedMetaData)) {
            return $fileModel;
        };

        return self::scrape($sourceUri, $destinationFile, $identifier, $serializedMetaData);
    }

    /**
     * @param string $destinationFile
     * @param string $identifier
     * @param string $serializedMetaData
     *
     * @return FilesModel|null
     */
    private static function updateResourceIfExists(
        string $destinationFile,
        string $identifier,
        string $serializedMetaData
    ) {
        // check if resource exists already
        if (null != ($fileModel =
                \FilesModel::findByPath(self::getUploadDirectory() . '/' . $destinationFile))
        ) {
            if ($serializedMetaData != $fileModel->meta) {
                self::updateMetaData($fileModel, $identifier, $serializedMetaData);
            }
            return $fileModel;
        }
        return null;
    }

    /**
     * @param $sourceUri
     * @param $destinationFile
     * @param $identifier
     * @param $serializedMetaData
     *
     * @return FilesModel|null
     */
    private static function scrape($sourceUri, $destinationFile, $identifier, $serializedMetaData)
    {
        // setup directory & download it
        if (!self::checkDirExists(self::getUploadDirectory(true) . '/' . self::facebookUploadSubDir)) {
            return null;
        }
        if (!self::downloadFile($sourceUri, self::getUploadDirectory(true) . '/' . $destinationFile)) {
            return null;
        }

        // add the uploaded file to the db filesystem & add meta data
        $fileModel = Dbafs::addResource(self::getUploadDirectory() . '/' . $destinationFile);
        if (null == $fileModel) {
            return null;
        }

        self::updateMetaData($fileModel, $identifier, $serializedMetaData);
        return $fileModel;
    }

    /**
     * @param string $objectId
     * @param string $type
     *
     * @return string|null
     */
    private static function getSourceUri(string $objectId, string $type)
    {
        // only 'photo' and 'event' supported
        if ('photo' != $type && 'event' != $type) {
            return null;
        }

        if ('event' == $type) {
            $cover = OpenGraph::queryObject($objectId, ['cover']);
            if (null != $cover && is_array($cover) && array_key_exists('cover', $cover)
                && is_array($cover['cover'])
                && array_key_exists('id', $cover['cover'])
            ) {
                $objectId = $cover['cover']['id'];
            } else {
                return null;
            }
        }

        // get available images
        $arrData = OpenGraph::queryObject($objectId, ['images']);
        if (null == $arrData || !is_array($arrData) || !array_key_exists('images', $arrData)) {
            return null;
        }

        // get source uri of biggest image
        $sourceUri = self::getBiggestImageSource($arrData['images']);
        if ('' == $sourceUri) {
            return null;
        }

        return $sourceUri;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    private static function getBiggestImageSource(array $data)
    {
        $widthLimit  = Config::get('gdMaxImgWidth');
        $heightLimit = Config::get('gdMaxImgHeight');

        $maxHeight = 0;
        $source    = '';

        /** @var GraphNode $graphNode */
        foreach ($data as $item) {
            $height = array_key_exists('height', $item) ? $item['height'] : 0;
            $width  = array_key_exists('width', $item) ? $item['width'] : 0;

            if ($height > $maxHeight && $height <= $heightLimit && $width <= $widthLimit) {
                $maxHeight = $height;
                $source    = array_key_exists('source', $item) ? $item['source'] : '';
            }
        }

        return $source;
    }

    /**
     * @param bool $absolutePath
     *
     * @return mixed|null|string
     */
    private static function getUploadDirectory(bool $absolutePath = false)
    {
        $contaoUploadDir = Config::get('uploadPath');

        if (!$absolutePath) {
            return $contaoUploadDir;
        }

        $projectDir = System::getContainer()->getParameter('kernel.project_dir');
        return $projectDir . '/' . $contaoUploadDir;
    }

    /**
     * @param string $identifier
     * @param bool   $addExtension
     *
     * @return string
     */
    private static function getUploadDestination(string $identifier, bool $addExtension = true)
    {
        return self::facebookUploadSubDir . '/' . ($addExtension ? sprintf('%s.jpg', $identifier) : $identifier);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    private static function checkDirExists($path)
    {
        try {
            if (!is_dir($path)) {
                mkdir($path);
            }
        } catch (Exception $e) {
            self::logError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @param $uriFrom
     * @param $pathTo
     *
     * @return bool
     */
    private static function downloadFile($uriFrom, $pathTo)
    {
        $client = new Client();

        try {
            // remove file if already existing
            if (file_exists($pathTo)) {
                unlink($pathTo);
            }

            // synchronous download
            $client->send(
                new Request('get', $uriFrom),
                [
                    'sink' => $pathTo
                ]
            );
        } catch (Exception $e) {
            self::logError($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @param FilesModel $fileModel
     * @param string     $name
     * @param string     $metaDescription
     */
    private static function updateMetaData(FilesModel $fileModel, string $name, string $metaDescription)
    {
        $fileModel->name = $name;
        $fileModel->meta = $metaDescription;
        $fileModel->save();
    }

    /**
     * @param $str
     */
    private static function logError($str)
    {
        $logger = System::getContainer()->get('monolog.logger.contao');

        $logger->log(
            LogLevel::ERROR,
            $str,
            array('contao' => new ContaoContext(debug_backtrace()[1]['function'], TL_ERROR))
        );
    }
}