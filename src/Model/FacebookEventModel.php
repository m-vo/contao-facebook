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
 * @property string  $eventId
 * @property string  $name
 * @property string  $description
 * @property integer $startTime
 * @property string  $locationName
 * @property string  $image
 * @property string  $ticketUri
 * @property integer $lastChanged
 *
 */
class FacebookEventModel extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $strTable = 'tl_mvo_facebook_event';

    /**
     * @return int
     */
    public static function getLastTimestamp()
    {
        $objResult = Database::getInstance()->execute(
            "SELECT tstamp FROM tl_mvo_facebook_event ORDER BY tstamp DESC LIMIT 1"
        );

        return (0 == $objResult->numRows) ? 0 : $objResult->tstamp;
    }
}
