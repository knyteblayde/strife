<?php namespace Kernel;

/**
 * Class Log
 *
 * @package Kernel
 */
class Log
{
    /**
     * log constructor
     *
     * @param $message
     * @param string $file
     * @return self
     */
    public function __construct($message, $file = 'logs.txt')
    {
        $file = fopen(STORAGE_PATH . "logs/" . $file, 'a');

        if (fwrite($file, Date("m-d-Y") . ' at ' . Date("h:i A") . ' ' . $message . "\n")) {
            $result = true;
        } else {
            $result = false;
        }

        return ($result);
    }
}