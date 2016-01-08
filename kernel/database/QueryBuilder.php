<?php namespace Kernel\Database;
/**
 * Interface Builder
 *
 * @package Kernel\Database
 */
interface QueryBuilderInterface
{
    public function __invoke();

    public function __get($property);

    public function __set($field, $value);

    public static function transact();

    public static function commit();

    public static function rollback();

    public static function inTransaction();

    public static function errorInfo();

    public static function quote($string);

    public static function save();

    public static function insert($valuePairs = []);

    public static function createFrom($valuePairs = []);

    public static function createExcept($valuePairs = [], $exception = []);

    public static function update($id, $valuePairs = []);

    public static function delete();

    public static function select($selection);

    public static function where($field, $a = null, $b = null);

    public static function whereIn($column, $values = []);

    public static function orWhere($field, $a = null, $b = null);

    public static function whereBetween($column, $first, $second);

    public static function orWhereBetween($column, $first, $second);

    public static function orderBy($field, $order);

    public static function limit($number);

    public static function find($id);

    public static function exists();

    public static function lastRow();

    public static function firstRow();

    public static function pull($column);

    public static function check();

    public static function dump();

    public static function lastInsertedId();

    public static function count();

    public static function distinct($fields);

    public static function first();

    public static function get($fetchMode = PDO::FETCH_OBJ);

    public static function parseQuery();

    public static function csvEncode($delimiter = ",", $separator = "|");

    public static function jsonEncode();
}

use PDO;
use PDOException;
use Kernel\Formatter;
use Kernel\Exceptions\ExceptionMessages;
use Kernel\Exceptions\UndefinedPropertyException;
use Kernel\Exceptions\InvalidMethodCallException;

/**
 * Query Builder provides robust database active record management
 * comprises with various set of useful methods for dealing with
 * database.
 *
 * Class QueryBuilder
 *
 * @package Kernel\Database
 */
class QueryBuilder extends Connection implements QueryBuilderInterface
{
    /**
     * This will be the query string to be populated
     */
    protected static $query = array();

    /**
     * Holds array of dynamic properties associated to model
     */
    protected static $fields = array();

    /**
     * Holds passed in values from builder
     */
    protected static $values = array();

    /**
     * Container for query result whenever a new instance of this class is generated.
     */
    protected static $result = array();

    /**
     * Table name inherited from model class
     */
    protected static $table = null;


    /**
     * For whenever class is called like a function
     * return the result set.
     * this will only return value if a successful
     * query returns an object directed to $result
     *
     * @return object
     */
    public function __invoke()
    {
        return (self::$result);
    }


    /**
     * Returns a value from $result object if dynamic $property given is
     * a property of it.
     *
     * @param $property
     * @return mixed
     * @throws UndefinedPropertyException
     */
    public function __get($property)
    {
        if (property_exists(self::$result, $property)) {
            return self::$result->$property;
        } else {
            return ("");
        }
    }


    /**
     * Setter method handles dynamically created properties along
     * with its value passed into it.
     *
     * @param $field
     * @param $value
     */
    public function __set($field, $value)
    {
        self::$fields[$field] = $value;
    }


    /**
     * Begin transaction, turns off auto-commit mode to prevent changes made to
     * the database until commit() method is called.
     *
     * @return boolean
     */
    public static function transact()
    {
        return self::getInstance()->beginTransaction();
    }


    /**
     * Turns back on the auto-commit mode and changes to database are not held back.
     *
     * @return boolean
     */
    public static function commit()
    {
        return self::getInstance()->commit();
    }


    /**
     * Roll back changes made to database right after the begin transaction and a query next
     * to it is present then brings back on auto-commit mode.
     *
     * @return boolean
     */
    public static function rollback()
    {
        return self::getInstance()->rollback();
    }


    /**
     * Check whether a transaction is currently active
     * returns boolean.
     *
     * @return boolean
     */
    public static function inTransaction()
    {
        return self::getInstance()->inTransaction();
    }


    /**
     * Return array of last error on operation if present.
     *
     * @return mixed
     */
    public static function errorInfo()
    {
        self::setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);

        return self::getInstance()->errorInfo();
    }


    /**
     * Wrap the string with quotes.
     *
     * @param $string
     * @return string
     */
    public static function quote($string)
    {
        return self::getInstance()->quote($string);
    }


    /**
     * Insert query, Accepts array key => value
     * pairs and binds each fields to raw query
     *
     * @param array $valuePairs
     * @return mixed
     * @throws InvalidMethodCallException
     */
    public static function insert($valuePairs = [])
    {
        try {
            if (empty($valuePairs)) {
                throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
            }

            $fields = trim(Formatter::arrayConcat(array_keys($valuePairs), ","), ',');
            $values = "";

            for ($i = 0; $i < count($valuePairs); $i++) {
                $values .= '?,';
            }
            $stmt = self::getInstance()->prepare("INSERT INTO " . static::$table . "({$fields}) VALUES(" . trim($values, ',') . ")");

            return $stmt->execute(array_values($valuePairs));
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Insert all from a set of array values
     *
     * @param array $valuePairs
     * @return bool
     * @throws InvalidMethodCallException
     */
    public static function createFrom($valuePairs = [])
    {
        if (empty($valuePairs)) {
            throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
        }

        return self::insert($valuePairs);
    }


    /**
     * Insert all from a set of array values
     * except for those in the exceptions.
     *
     * @param array $valuePairs
     * @param array $exceptions
     * @return bool
     * @throws InvalidMethodCallException
     */
    public static function createExcept($valuePairs = [], $exceptions = [])
    {
        if (empty($valuePairs) || empty($exceptions)) {
            throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
        }

        for ($i = 0; $i < count(array_values($exceptions)); $i++) {
            if (array_key_exists(array_values($exceptions)[$i], $valuePairs)) {
                unset($valuePairs[array_values($exceptions)[$i]]);
            }
        }

        return self::insert($valuePairs);
    }


    /**
     * saves the $fields if keys reflects the fields
     * in the database.
     *
     * @throws InvalidMethodCallException
     */
    public static function save()
    {
        $result = false;

        if (self::update(self::$fields, self::$result->id)) {
            $result = true;
            foreach (self::$fields as $field => $value) {
                if (property_exists(self::$result, $field)) {
                    self::$result->$field = self::where('id', self::$result->id)->pull($field);
                }
            }
        }
        self::$fields = [];

        return ($result);
    }


    /**
     * Update the given set of field => value pairs
     * from $fields array.
     *
     * @param array $valuePairs
     * @param null $id
     * @return mixed
     * @throws InvalidMethodCallException
     */
    public static function update($valuePairs = [], $id = null)
    {
        try {
            if (empty($valuePairs)) {
                throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
            }

            $id = (!is_null($id)) ? $id : self::$result->id;

            $params = trim(Formatter::arrayConcat(array_keys($valuePairs), '=?,'), ',');
            $stmt = self::getInstance()->prepare("UPDATE " . static::$table . " SET {$params} WHERE id={$id}");

            return $stmt->execute(array_values($valuePairs));
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }


    /**
     * Deletes a single row(if given param is single id).
     * if more than one param supplied, it will delete it recursively.
     * it is also possible to be chained with where statement
     *
     * @param null $id
     * @return mixed
     */
    public static function delete($id = null)
    {
        try {
            self::$table = static::$table;
            if (is_null($id) && !isset(self::$result)) {
                self::$query['select'] = 'DELETE ';
                $stmt = self::getInstance()->prepare(self::parseQuery());
                $stmt = $stmt->execute(self::$values);
                self::$values = [];
                self::$query = [];

                return ($stmt);
            } else {
                $id = (!is_null($id)) ? $id : self::$result->id;
            }

            if (count(func_get_args()) > 1) {
                foreach (func_get_args() as $arg) {
                    $stmt = self::getInstance()->exec("DELETE FROM " . static::$table . " WHERE id={$arg};");
                }
            } else {
                $stmt = self::getInstance()->exec("DELETE FROM " . static::$table . " WHERE id={$id};");
            }

            return ($stmt);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Select database columns.
     * if number of arguments supplied is greater than one, it will be handled
     * recursively, otherwise treats the single argument as the selection.
     * e.g. select('name,age') or select('name','age') or select('*')
     *
     * @param $columns
     * @return QueryBuilder
     * @throws InvalidMethodCallException
     */
    public static function select($columns)
    {
        if (empty($columns)) {
            throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
        }

        self::$table = static::$table;
        $selection = "";
        if (count(func_get_args()) > 1) {
            foreach (func_get_args() as $arg) {
                $selection .= $arg . ",";
            }
        } else {
            $selection = $columns;
        }

        self::$query['select'] = trim($selection, ',');

        return new self;
    }


    /**
     * where statement, can be chained next to it.
     *
     * @param $field
     * @param null $a
     * @param null $b
     * @return self
     * @throws InvalidMethodCallException
     */
    public static function where($field, $a = null, $b = null)
    {
        if (empty($field) || empty($a)) {
            throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
        }

        self::$table = static::$table;
        $param = ($b == null) ? "= ?" : "{$a} ?";

        self::$values[] = ($b == null) ? $a : $b;

        self::$query['where'] = (isset(self::$query['where']) && preg_match('/WHERE/i', self::$query['where']))
            ? self::$query['where'] . " AND {$field} {$param}" : "WHERE {$field} {$param}";

        return new self;
    }


    /**
     * Selects a row with value that is equal to
     * second argument.
     *
     * @param $column
     * @param array $values
     * @return self
     * @throws InvalidMethodCallException
     */
    public static function whereIn($column, $values = [])
    {
        if (empty($column) || empty($values)) {
            throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
        }

        self::$table = static::$table;
        $params = "";

        foreach ($values as $value) {
            $params .= "?,";
            self::$values[] = $value;
        }

        $params = trim($params, ',');

        self::$query['where'] = (isset(self::$query['where']) && preg_match('/WHERE/i', self::$query['where']))
            ? self::$query['where'] . " AND {$column} IN ({$params})" : "WHERE {$column} IN ({$params})";

        return new self;
    }


    /**
     * Selects a row with value that is equal to
     * second argument.
     *
     * @param $column
     * @param $first
     * @param $second
     * @return self
     * @throws InvalidMethodCallException
     */
    public static function whereBetween($column, $first, $second)
    {
        if (empty($column) || empty($first) || empty($second)) {
            throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
        }

        self::$table = static::$table;
        self::$values[] = $first;
        self::$values[] = $second;

        self::$query['where'] = (isset(self::$query['where']) && preg_match('/WHERE/i', self::$query['where']))
            ? self::$query['where'] . " AND {$column} BETWEEN ? AND ?" : "WHERE {$column} BETWEEN ? AND ?";

        return new self;
    }


    /**
     * Selects a row with value that is equal to
     * second argument.
     *
     * @param $column
     * @param $first
     * @param $second
     * @return self
     * @throws InvalidMethodCallException
     */
    public static function orWhereBetween($column, $first, $second)
    {
        if (empty($column) || empty($first) || empty($second)) {
            throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
        }

        self::$table = static::$table;
        self::$values[] = $first;
        self::$values[] = $second;

        self::$query['where'] = (isset(self::$query['where']) && preg_match('/WHERE/i', self::$query['where']))
            ? self::$query['where'] . " OR {$column} BETWEEN ? AND ?" : trigger_error("orWhereBetween() should be right next to where() method.", E_USER_ERROR);

        return new self;
    }


    /**
     * Similar to WHERE but prepends an OR.
     *
     * @param $field
     * @param null $a
     * @param null $b
     * @return self
     * @throws InvalidMethodCallException
     */
    public static function orWhere($field, $a = null, $b = null)
    {
        if (empty($field) || empty($a)) {
            throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
        }

        self::$table = static::$table;
        $param = ($b == null) ? "= ?" : "{$a} ?";

        self::$values[] = ($b == null) ? $a : $b;

        self::$query['where'] = (isset(self::$query['where']) && preg_match('/WHERE/i', self::$query['where']))
            ? self::$query['where'] . " OR {$field} {$param}" : trigger_error("'orWhere()' should be right next to 'where()' method.", E_USER_ERROR);

        return new self;
    }


    /**
     * ORDER BY statement
     *
     * @param $field
     * @param $order
     * @return self
     * @throws InvalidMethodCallException
     */
    public static function orderBy($field, $order)
    {
        if (empty($field) || empty($order)) {
            throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
        }

        self::$table = static::$table;
        self::$query['orderBy'] = "ORDER BY $field " . strtoupper($order);

        return new self;
    }


    /**
     * limit statement where $limit is the number
     * of rows to be returned.
     *
     * @param $limit
     * @return self
     * @throws InvalidMethodCallException
     */
    public static function limit($limit)
    {
        if (isset($limit) && !is_numeric($limit)) {
            throw new InvalidMethodCallException(ExceptionMessages::NOT_INT);
        }

        self::$table = static::$table;
        self::$query['limit'] = "LIMIT {$limit}";

        return new self;
    }


    /**
     * Print the constructed $query
     *
     * @return string
     */
    public static function check()
    {
        return die("<code>" . strtolower(self::parseQuery()) . "</code>");
    }


    /**
     * Dump the result set into readable format.
     *
     * @return string
     */
    public static function dump()
    {
        return dump(self::$result);
    }


    /**
     * find a single row from database using the given id.
     * and return an object
     *
     * @param $id
     * @return self|bool
     * @throws InvalidMethodCallException
     */
    public static function find($id)
    {
        try {
            if (isset($id) && !is_numeric($id)) {
                throw new InvalidMethodCallException(ExceptionMessages::NOT_INT);
            }

            self::$table = static::$table;
            $stmt = self::getInstance()->prepare("SELECT * FROM " . static::$table . " WHERE id=? LIMIT 1");
            $stmt->execute([$id]);

            if ($stmt->rowCount() == 0) {
                return false;
            }

            self::$result = $stmt->fetch(PDO::FETCH_OBJ);

            return new self;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Pull a single row from database.
     *
     * @param $column
     * @return mixed
     * @throws InvalidMethodCallException
     */
    public static function pull($column)
    {
        try {
            if (empty($column)) {
                throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
            }
            self::$query['select'] = $column;
            self::$query['limit'] = "LIMIT 1";
            $stmt = self::getInstance()->prepare(self::parseQuery());
            $stmt->execute(array_values(self::$values));
            self::$values = [];
            self::$query = [];

            return $stmt->fetch(PDO::FETCH_OBJ)->$column;

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Determines whether a value exists
     * in the database.
     *
     * @return bool
     */
    public static function exists()
    {
        try {
            $stmt = self::getInstance()->prepare(self::parseQuery());
            $stmt->execute(array_values(self::$values));
            self::$values = [];
            self::$query = [];

            return ($stmt->rowCount() == 0) ? false : true;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Return that last row on the database
     *
     * @return QueryBuilder
     */
    public static function lastRow()
    {
        try {
            self::$table = static::$table;
            $stmt = self::getInstance()->prepare("SELECT * FROM ".static::$table." ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            self::$result = $stmt->fetch(PDO::FETCH_OBJ);

            return new self;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Return that last row on the database
     *
     * @return QueryBuilder
     */
    public static function firstRow()
    {
        try {
            self::$table = static::$table;
            $stmt = self::getInstance()->prepare("SELECT * FROM ".static::$table." ORDER BY id ASC LIMIT 1");
            $stmt->execute();
            self::$result = $stmt->fetch(PDO::FETCH_OBJ);

            return new self;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }



    /**
     * Return that last inserted id
     * from database.
     * NOTE: this can only be fetched right after the active query
     * is being carried out.
     *
     * @return mixed
     */
    public static function lastInsertedId()
    {
        try {
            return self::getInstance()->lastInsertId();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Count rows returned from query
     *
     * @return mixed
     */
    public static function count()
    {
        try {
            $stmt = self::getInstance()->prepare(self::parseQuery());
            $stmt->execute(array_values(self::$values));
            self::$values = [];
            self::$query = [];

            return $stmt->rowCount();
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Get distinctive values
     *
     * @param $fields
     * @return self|bool
     * @throws InvalidMethodCallException
     */
    public static function distinct($fields)
    {
        if (empty($fields)) {
            throw new InvalidMethodCallException(ExceptionMessages::EMPTY_VALUE);
        }

        self::$table = static::$table;
        $selection = "";
        if (count(func_get_args()) > 1) {
            foreach (func_get_args() as $arg) {
                $selection .= $arg . ",";
            }
        } else {
            $selection = $fields;
        }

        self::$query['select'] = "DISTINCT " . trim($selection, ',');

        return new self;
    }


    /**
     * Return the single row from database
     *
     * @return self|bool
     */
    public static function first()
    {
        try {
            self::$table = static::$table;
            $stmt = self::getInstance()->prepare(self::parseQuery());
            $stmt->execute(array_values(self::$values));
            self::$values = [];
            self::$query = [];
            self::$result = $stmt->fetch(PDO::FETCH_OBJ);

            return new self;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Executes a constructed query string or
     * uses default selection if not present.
     * returns default object
     *
     * @param int $fetchMode
     * @var $stmt
     * @return mixed
     * @throws PDOException
     */
    public static function get($fetchMode = PDO::FETCH_OBJ)
    {
        try {
            $stmt = self::getInstance()->prepare(self::parseQuery());
            $stmt->execute(array_values(self::$values));
            self::$values = [];
            self::$query = [];

            return $stmt->fetchAll($fetchMode);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Parse the query string
     *
     * @var $selection
     * @var $whereStmt
     * @var $orderBy
     * @var $limit
     * @return string
     */
    public static function parseQuery()
    {
        if (isset(self::$query['select'])) {
            if (preg_match('/DELETE/i', self::$query['select'])) {
                $selection = self::$query['select'];
            } else {
                $selection = "SELECT " . self::$query['select'];
            }
        } else {
            $selection = "SELECT *";
        }

        $whereStmt = (isset(self::$query['where'])) ? self::$query['where'] : '';
        $orderBy = (isset(self::$query['orderBy'])) ? self::$query['orderBy'] : '';
        $limit = (isset(self::$query['limit'])) ? self::$query['limit'] : '';

        return ("{$selection} FROM " . static::$table . " {$whereStmt} {$orderBy} {$limit}");
    }


    /**
     * Concat values to CSV
     *
     * @param string $delimiter
     * @param string $separator
     * @return string
     */
    public static function csvEncode($delimiter = ',', $separator = "|")
    {
        try {
            $delimiter = (!empty($delimiter)) ? $delimiter : ',';
            $separator = (!empty($separator)) ? $separator : '|';

            $results = self::get(PDO::FETCH_ASSOC);
            $values = "";

            foreach ($results as $result) {
                $values .= trim(Formatter::arrayConcat($result, $delimiter), $delimiter) . "{$separator}";
            }

            return trim($values, '|');
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Converts a fetched data into JSON format
     *
     * @return string
     */
    public static function jsonEncode()
    {
        try {
            return json_encode(self::get(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}