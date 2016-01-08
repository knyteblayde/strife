<?php namespace Kernel\Database;

use PDO;
use PDOException;

/**
 * Connection class provides a single
 * instance of the connection to its child
 * classes.
 *
 * Class Connection
 *
 * @package Kernel\Database
 */
class Connection
{

    /**
     * Holds the PDO object
     **/
    protected static $pdo = null;

    /**
     * Connection Credentials
     **/
    private static $cxn = array();


    /**
     * Returns single instance of
     * PDO object.
     *
     * @return mixed
     **/
    public static function getInstance()
    {
        if (is_null(self::$pdo)) {
            self::initialize();
        }
        return self::$pdo;
    }


    /**
     * Set connection credentials
     * as array() and store to self::$cxn
     *
     * @param $cxn = []
     * @return mixed
     **/
    public static function parameters($cxn = array())
    {
        self::$cxn = $cxn;

        return new self;
    }


    /**
     * Initialize connection: set self::$pdo
     * to new instance of PDO object.
     *
     * @todo connect to database
     * @param int transact
     * @return mixed
     **/
    public static function initialize($transact = 1)
    {
        if (!$transact) {
            return false;
        }
        try {
            if (is_null(self::$pdo)) {
                self::$pdo = new PDO(
                    self::$cxn['driver'] . ":hostname=" .
                    self::$cxn['hostname'] . ";dbname=" .
                    self::$cxn['database'] . ";port=" .
                    self::$cxn['port'] . ";charset=" .
                    self::$cxn['charset'],
                    self::$cxn['username'],
                    self::$cxn['password']
                );
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Set PDO's attribute.
     *
     * @param $attribute
     * @param $value
     * @return mixed
     **/
    public static function setAttribute($attribute, $value)
    {
        return self::$pdo->setAttribute($attribute, $value);
    }


    /**
     * Destroy Connection
     *
     * @return mixed
     **/
    public static function close()
    {
        return self::$pdo = null;
    }

}
