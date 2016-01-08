<?php namespace Kernel;

/**
 * Class Encryption
 * @package Kernel
 */
class Encryption
{

    /**
     * In case you want to
     * use encryption to encoded/literal string.
     *
     * @param $data
     * @return string
     **/
    public static function encrypt($data)
    {
        return base64_encode(serialize($data));
    }


    /**
     * Decrypting encrypted value
     *
     * @param $data
     * @return string
     **/
    public static function decrypt($data)
    {
        return unserialize(base64_decode($data, true));
    }

}