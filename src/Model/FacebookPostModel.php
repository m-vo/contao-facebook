<?php

namespace Mvo\ContaoFacebook\Model;

use Contao\Database;
use Model;

/**
 * Reads and writes projects
 *
 * @property integer $id
 * @property integer $tstamp
 * @property bool    $visible
 *
 * @property string  $postId
 * @property integer $postTime
 * @property string  $message
 * @property string  $image
 * @property integer $lastChanged
 *
 */
class FacebookPostModel extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = 'tl_mvo_facebook_post';

    /**
     * @return int
     */
    public static function getLastTimestamp()
    {
        $objResult = Database::getInstance()->execute(
            "SELECT tstamp FROM tl_mvo_facebook_post ORDER BY tstamp DESC LIMIT 1"
        );

        return (0 == $objResult->numRows) ? 0 : $objResult->tstamp;
    }
}
