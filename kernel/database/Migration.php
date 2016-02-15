<?php namespace Kernel\Database;

use Exception;
use PDOException;

/**
 * Migration Manager
 *
 * Class Migration
 * @package Kernel\Database
 */
abstract class Migration extends Connection
{

    /**
     * property that will hold the table fields
     */
    protected $fields = array();

    /**
     * Sets a field to be an auto increment of type
     *
     * @param $name
     * @param $length
     * @return string
     **/
    protected function increments($name, $length = 11)
    {
        return $this->fields[$name] = "$name INT($length) UNSIGNED AUTO_INCREMENT PRIMARY KEY";
    }


    /**
     * String Data Types
     * some methods of this type intentionally removed the $unique param because of they
     * just don't have to be unique.
     *
     * @param $name
     * @param $length
     * @param $unique
     * @param $nullable
     * @return string
     **/
    protected function char($name, $length = 255, $unique = null, $nullable = "NOT NULL")
    {
        $nullable = ($nullable == "null") ? strtoupper($nullable) : $nullable;
        $params = ($unique == 'unique') ? "$name CHAR($length) $nullable, UNIQUE($name)" : "$name CHAR($length) NOT NULL";
        return $this->fields[$name] = $params;
    }

    /**
     * @param $name
     * @param int $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function varchar($name, $length = 255, $unique = null, $nullable = "NOT NULL")
    {
        $nullable = ($nullable == "null") ? strtoupper($nullable) : $nullable;
        $params = ($unique == 'unique') ? "$name VARCHAR($length) $nullable, UNIQUE($name)" : "$name VARCHAR($length) NOT NULL";
        return $this->fields[$name] = $params;
    }

    /**
     * @param $name
     * @param int $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function tinyText($name, $length = 255, $unique = null, $nullable = "NOT NULL")
    {
        $nullable = ($nullable == "null") ? strtoupper($nullable) : $nullable;
        $params = ($unique == 'unique') ? "$name TINYTEXT($length) $nullable, UNIQUE($name)" : "$name TINYTEXT($length) NOT NULL";
        return $this->fields[$name] = $params;
    }

    /**
     * @param $name
     * @param $nullable
     * @return string
     **/
    protected function text($name, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = "$name TEXT " . strtoupper($nullable);
    }

    /**
     * @param $name
     * @param string $nullable
     * @return string
     */
    protected function mediumText($name, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = "$name MEDIUMTEXT " . strtoupper($nullable);
    }

    /**
     * @param $name
     * @param string $nullable
     * @return string
     */
    protected function longText($name, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = "$name LONGTEXT " . strtoupper($nullable);
    }

    /**
     * @param $name
     * @param string $nullable
     * @return string
     */
    protected function binary($name, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = "$name BINARY " . strtoupper($nullable);
    }

    /**
     * @param $name
     * @param string $nullable
     * @return string
     */
    protected function varBinary($name, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = "$name VARBINARY " . strtoupper($nullable);
    }


    /**
     * Integers Data Types
     *
     * @param $name
     * @param $length
     * @param $unique
     * @param $nullable
     * @return string
     **/
    protected function tinyInt($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "TINYINT", $length, $unique, $nullable);
    }

    /**
     * Construct passed in INTEGER data types
     *
     * @param $name
     * @param $dataType
     * @param $length
     * @param $unique
     * @param $nullable
     *
     * @return string
     **/
    private function parseIntType($name, $dataType, $length, $unique, $nullable)
    {
        $length = (!is_null($length)) ? "($length)" : "";
        $unique = (!is_null($unique)) ? ", UNIQUE($name)" : "";

        return "{$name} {$dataType}{$length} {$nullable}{$unique}";
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function smallInt($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "SMALLINT", $length, $unique, $nullable);
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function integer($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "INTEGER", $length, $unique, $nullable);
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function mediumInt($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "MEDIUMINT", $length, $unique, $nullable);
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function bigInt($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "BIGINT", $length, $unique, $nullable);
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function decimal($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "DECIMAL", $length, $unique, $nullable);
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function float($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "FLOAT", $length, $unique, $nullable);
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function double($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "DOUBLE", $length, $unique, $nullable);
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function real($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "REAL", $length, $unique, $nullable);
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function bit($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "BIT", $length, $unique, $nullable);
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function boolean($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "BOOLEAN", $length, $unique, $nullable);
    }

    /**
     * @param $name
     * @param null $length
     * @param null $unique
     * @param string $nullable
     * @return string
     */
    protected function serial($name, $length = null, $unique = null, $nullable = "NOT NULL")
    {
        return $this->fields[$name] = $this->parseIntType($name, "SERIAL", $length, $unique, $nullable);
    }



    /**
     * Time and Date Data Types
     **/

    /**
     * @param $name
     * @param $nullable
     * @return string
     **/
    protected function date($name, $nullable = "NOT NULL")
    {
        $nullable = ($nullable == "null") ? strtoupper($nullable) : $nullable;
        return $this->fields[$name] = "$name DATE $nullable";
    }

    /**
     * @param $name
     * @param string $nullable
     * @return string
     */
    protected function datetime($name, $nullable = "NOT NULL")
    {
        $nullable = ($nullable == "null") ? strtoupper($nullable) : $nullable;
        return $this->fields[$name] = "$name DATETIME $nullable";
    }

    /**
     * @param $name
     * @param string $nullable
     * @return string
     */
    protected function timestamp($name, $nullable = "NOT NULL")
    {
        $nullable = ($nullable == "null") ? strtoupper($nullable) : $nullable;
        return $this->fields[$name] = "$name TIMESTAMP $nullable";
    }

    /**
     * @param $name
     * @param string $nullable
     * @return string
     */
    protected function time($name, $nullable = "NOT NULL")
    {
        $nullable = ($nullable == "null") ? strtoupper($nullable) : $nullable;
        return $this->fields[$name] = "$name TIME $nullable";
    }

    /**
     * @param $name
     * @param string $nullable
     * @return string
     */
    protected function year($name, $nullable = "NOT NULL")
    {
        $nullable = ($nullable == "null") ? strtoupper($nullable) : $nullable;
        return $this->fields[$name] = "$name YEAR $nullable";
    }


    /**
     * Installation of migration creates a table depending on
     * the migration class' shared $table property along with
     * the fields that should have been setup beforehand
     *
     * @var $values
     * @var $params
     * @return void
     * @throws Exception
     **/
    protected function install()
    {
        try {
            if (empty($this->fields)) throw new Exception("Cannot use an value for reading");

            $values = array_values($this->fields);
            $params = "";

            for ($i = 0; $i < count($values); $i++) {
                $params .= "$values[$i],";
            }

            self::$pdo->exec("DROP TABLE IF EXISTS {$this->table}");
            self::$pdo->exec("CREATE TABLE {$this->table}(" . trim($params, ',') . ")");

            return ("Table '{$this->table}' migrated.");
        } catch (PDOException $e) {
            print $e->getMessage();
        }
    }


    /**
     * Drop the table
     *
     * @var $message
     * @return void
     **/
    protected function uninstall()
    {
        try {
            self::$pdo->exec("DROP TABLE IF EXISTS {$this->table}");

            return ("Table '{$this->table}' rolled back.");
        } catch (PDOException $e) {
            print $e->getMessage();
        }
    }


    /**
     * Return the array containing values
     * formulated through its migration class.
     *
     * @return array
     */
    public function dictionary()
    {
        return ($this->fields);
    }
}