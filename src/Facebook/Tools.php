<?php

namespace Mvo\ContaoFacebook\Facebook;

class Tools
{
    /**
     * @param string $str
     *
     * @return string
     */
    public static function encodeText(string $str)
    {
        return utf8_encode($str);
    }

    /**
     * @param string $str
     */
    public static function formatText(string $str)
    {
        $str = self::decode($str);
        $str = self::replaceUrls($str);

        return self::formatWhitespaces($str);
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private static function decode(string $str)
    {
        return utf8_decode($str);
    }

    /**
     * @param string $str
     *
     * @return mixed
     */
    private static function replaceUrls(string $str)
    {
        // surround urls with <a> tags
        return preg_replace("#http://([\S]+?)#Uis", '<a rel="nofollow" href="http://\\1">\\1</a>', $str);
    }

    /**
     * @param string $str
     *
     * @return mixed
     */
    private static function formatWhitespaces(string $str)
    {
        return nl2br_html5(str_replace('  ', '&nbsp;&nbsp;', $str));
    }
}