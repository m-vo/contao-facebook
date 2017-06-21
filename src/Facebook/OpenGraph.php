<?php

namespace Mvo\ContaoFacebook\Facebook;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Facebook\Authentication\AccessToken;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookRequest;
use Psr\Log\LogLevel;

class OpenGraph
{
    /**
     * @param int   $objectId
     * @param array $fieldNames
     * @param array $params
     */
    public static function queryObject(string $objectId, array $fieldNames, array $params = [])
    {
        $response = self::performRequest($objectId, $fieldNames, $params, true);
        return (null !== $response) ? $response->getDecodedBody() : null;
    }

    /**
     * @param string $entity     the entity to query against (post, events, ...)
     * @param array  $fieldNames field names to query
     * @param array  $params     add custom params if needed
     *
     * @return \Facebook\GraphNodes\GraphEdge
     */
    public static function queryEdge(string $entity, array $fieldNames, array $params = [])
    {
        $response = self::performRequest($entity, $fieldNames, $params);

        try {
            $edge = $response->getGraphEdge();

        } catch (FacebookSDKException $e) {
            // validation failed or other local issues
            self::logError('Facebook SDK returned an error: ' . $e->getMessage());
            return null;

        } catch (\Exception $e) {
            self::logError('Unknown error: ' . $e->getMessage());
            return null;
        }

        return $edge;
    }


    private static function performRequest(string $entity, array $fieldNames, array $params = [], $noPagedQuery = false)
    {
        $container = System::getContainer();

        $fb = new Facebook(
            [
                'app_id'                => $container->getParameter('mvo_contao_facebook.app_id'),
                'app_secret'            => $container->getParameter('mvo_contao_facebook.app_secret'),
                'default_graph_version' => 'v2.9',
            ]
        );

        $accessToken = new AccessToken($container->getParameter('mvo_contao_facebook.access_token'));
        $pageName    = $container->getParameter('mvo_contao_facebook.fb_page_name');

        // perform request
        $query = ($noPagedQuery == false)
            ?
            sprintf('%s/%s?fields=%s', $pageName, $entity, implode(',', $fieldNames))
            :
            sprintf('%s?fields=%s', $entity, implode(',', $fieldNames));

        $request = new FacebookRequest(
            $fb->getApp(),
            $accessToken->getValue(),
            'GET',
            $query,
            $params
        );

        try {
            $response = $fb->getClient()->sendRequest($request);
            // todo: follow paginated entries

        } catch (FacebookResponseException $e) {
            // graph returned an error
            self::logError('Graph returned an error: ' . $e->getMessage());
            return null;

        } catch (FacebookSDKException $e) {
            // validation failed or other local issues
            self::logError('Facebook SDK returned an error: ' . $e->getMessage());
            return null;

        } catch (\Exception $e) {
            self::logError('Unknown error: ' . $e->getMessage());
            return null;
        }

        return $response;
    }

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