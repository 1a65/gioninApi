<?php

namespace Gionin;

/**
 * Class for UTF-8 encoding handling
 *
 * @package Gionin
 * @author  Raphael Giovanini
 **/
class Utf8String
{
    /**
     * Test if string is UTF-8 encoded
     *
     * @param string $text Any string
     * @return bool
     **/
    public static function isUTF8(string $text): bool
    {
        return mb_check_encoding($text, 'UTF-8');
    }

    /**
     * Recursive decode from UTF-8 to ISO-8859-1
     *
     * @param string $text Any string
     * @return string The decoded string
     **/
    public static function recursiveDecode(string $text): string
    {
        while (self::isUTF8($text)) {
            $decoded = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
            if ($decoded === $text) {
                break;
            }
            $text = $decoded;
        }
        return $text;
    }

    /**
     * Rebase encode to UTF-8
     *
     * @param string $text Any string
     * @return string The UTF-8 encoded string
     **/
    public static function rebaseEncode(string $text): string
    {
        return mb_convert_encoding(self::recursiveDecode($text), 'UTF-8', 'ISO-8859-1');
    }

    /**
     * Remove accentuation
     *
     * @param string $text Any string
     * @return string The string without accents
     **/
    public static function noAccents(string $text): string
    {
        return preg_replace(
            [
                '/&szlig;/',
                '/&(..)lig;/',
                '/&([aouAOU])uml;/',
                '/&(.)[^;]*;/',
            ],
            [
                'ss',
                "$1",
                "$1",
                "$1",
            ],
            htmlentities(self::rebaseEncode($text), ENT_COMPAT, 'UTF-8')
        );
    }

    /**
     * Make a string lowercase and remove accentuation
     *
     * @param string $text Any string
     * @return string Lowercase string without accents
     **/
    public static function lowerAndNoAccents(string $text = ''): string
    {
        return strtolower(self::noAccents($text));
    }
}
