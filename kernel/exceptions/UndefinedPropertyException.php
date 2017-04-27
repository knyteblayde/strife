<?php namespace Kernel\Exceptions;

use Exception;

/**
 * Class UndefinedPropertyException
 */
class UndefinedPropertyException extends Exception
{
    public function __construct($property)
    {
        $this->message = "Undefined property $property";
    }
}