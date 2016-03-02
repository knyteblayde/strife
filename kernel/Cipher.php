<?php namespace Kernel;

/**
 * Class Cipher
 *
 * @package Kernel
 */
class Cipher
{
    /**
     * @var string
     */
    private static $alphabet = [
        "a", "b", "c", "d", "e",
        "f", "g", "h", "i", "j",
        "k", "l", "m", "n", "o",
        "p", "q", "r", "s", "t",
        "u", "v", "w", "x", "y", "z"
    ];


    /**
     * Encrypt
     *
     * @param $string
     * @return string
     */
    public static function encrypt($string)
    {
        $string = str_split(strtolower($string));
        $flipped = array_flip(self::$alphabet);
        $encrypted = "";

        for ($i = 0; $i < count($string); $i++) {
            $encrypted .= self::$alphabet[($flipped[$string[$i]]+5) % 26];
        }

        return ($encrypted);
    }


    /**
     * Decrypt
     *
     * @param $string
     * @return string
     */
    public static function decrypt($string)
    {
        $string = str_split(strtolower($string));
        $flipped = array_flip(self::$alphabet);
        $decrypted = "";

        for ($i = 0; $i < count($string); $i++) {
            $decrypted .= self::$alphabet[(26 + $flipped[$string[$i]]-5) % 26];
        }

        return ($decrypted);
    }
}