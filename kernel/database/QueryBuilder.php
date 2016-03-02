<?php namespace Kernel\Database;

/**********************************************************
 * Query Builder Class for Strife Framework.
 * provides handy set of methods for interacting with the
 * database and fully implements active record management
 *
 * Author:  Jeyz Strife
 * website: https://github.com/knyteblayde/strife
 * Date:    11/10/15
 */


/**
 * Interface MagicMethods
 *
 * @package Kernel\Database
 */
interface QueryBuilderMagicInterface
{
    public function __construct($data = null);

    public function __invoke();

    public function __call($name, $arguments);

    public static function __callStatic($name, $arguments);

    public function __get($property);

    public function __set($field, $value);
}

/**
 * Interface QueryBuilderInterface
 *
 * @package Kernel\Database
 */
interface QueryBuilderInterface
{
    public static function backup();

    public static function restore();

    public static function transact();

    public static function commit();

    public static function rollback();

    public static function inTransaction();

    public static function errorInfo();

    public static function quote($string);

    public function save();

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

    public static function increment($field, $int = null);

    public static function decrement($field, $int = null);

    public static function get($fetchMode = PDO::FETCH_OBJ);

    public static function csvEncode($delimiter = ",", $separator = "|");

    public static function jsonEncode();
}

use PDO;
use PDOException;
use Kernel\Formatter;
use Kernel\Encryption;
use Kernel\FileHandler;
use Kernel\Exceptions\ExceptionMessages;
use Kernel\Exceptions\InvalidMethodCallException;


/**
 * Class QueryBuilder
 *
 * @package Kernel\Database
 */
class QueryBuilder extends Connection implements QueryBuilderInterface, QueryBuilderMagicInterface
{
    /**
     * This will be the query string to be populated
     */
    protected static $query = array();

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
     * Holds dynamically created properties for editing
     * parsed from setter magic method.
     */
    private $fields = array();

    /**
     * Holds result set from find() and first()
     * methods.
     */
    public $original = array();


    /**
     * QueryBuilder constructor.
     * prevents __get() magic method
     * from changing the result set using first() and find()
     * method. store result set in public and static variables.
     *
     * @param $data
     */
    public function __construct($data = null)
    {
        if (!is_null($data)) {
            $this->original = $data;
            self::$result = $data;
        }
    }


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
     * Handles dynamically called undefined methods
     * on normal method call.
     *
     * @param $name
     * @param array $arguments
     * @return bool|QueryBuilder|mixed
     */
    public function __call($name, $arguments = [])
    {
        return self::callParser($name, $arguments);
    }


    /**
     * Handles dynamically called methods
     * on static call point.
     *
     * @param $name
     * @param $arguments
     * @return bool|QueryBuilder|mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::callParser($name, $arguments);
    }


    /**
     * Serves as a parser for bot __call and __callStatic
     * magic methods.
     * Handles dynamically created method that does not exist
     * in this class.
     * handles 'where', 'orWhere', 'increment', 'decrement', and 'pull'
     * triggers when undefined method is called statically or not
     * e.g. User::wherePassword('secret'), $user->wherePassword('secret')
     *
     * @param $name
     * @param $arguments
     * @return bool|QueryBuilder|mixed
     */
    private static function callParser($name, $arguments)
    {
        /**
         * We assume every undefined method that has 'where'
         * on it should return a where statement with a single variable passed in
         */
        if (preg_match('/^where/i', $name)) {
            $name = strtolower(preg_replace('/^where/i', '', $name));
            if (count($arguments) > 1) {
                return self::where($name, $arguments[0], $arguments[1]);
            } else {
                return self::where($name, $arguments[0]);
            }
        } /**
         * if $name has 'orWhere' matched, return
         * orWhere() method.
         */
        elseif (preg_match('/^orWhere/i', $name)) {
            $name = strtolower(preg_replace('/^orWhere/i', '', $name));
            if (count($arguments) > 1) {
                return self::orWhere($name, $arguments[0], $arguments[1]);
            } else {
                return self::orWhere($name, $arguments[0]);
            }
        } /**
         * if $name has 'increment' matched, return
         * increment() method.
         */
        elseif (preg_match('/^increment/i', $name)) {
            $name = strtolower(preg_replace('/^increment/i', '', $name));
            $int = isset($arguments[0]) ? $arguments[0] : 1;
            return self::increment($name, $int);
        } /**
         * if $name has 'decrement' matched, return
         * decrement() method.
         */
        elseif (preg_match('/^decrement/i', $name)) {
            $name = strtolower(preg_replace('/^decrement/i', '', $name));
            $int = isset($arguments[0]) ? $arguments[0] : 1;
            return self::decrement($name, $int);
        } /**
         * if $name has 'pull' matched, return
         * pull() method.
         */
        elseif (preg_match('/^pull/i', $name)) {
            $name = strtolower(preg_replace('/^pull/i', '', $name));
            return self::pull($name);
        } /**
         * orderBy Method
         */
        elseif (preg_match('/^orderBy/i', $name)) {
            $name = strtolower(preg_replace('/^orderBy/i', '', $name));
            return self::orderBy($name, $arguments[0]);
        } else {
            return false;
        }
    }


    /**
     * Returns a value from $result object if dynamic $property given is
     * a property of it.
     *
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this->original, $property)) {
            return $this->original->$property;
        } else {
            return (null);
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
        $this->fields[$field] = $value;
    }


    /**
     * Backup a database table.
     *
     * @return bool
     */
    public static function backup()
    {
        $file = new FileHandler(storage_dir() . 'backups/' . static::$table . ".json", 'w+');
        $data = self::jsonEncode();
        if ($file->write($data, 'w')) {
            return (true);
        } else {
            return (false);
        }
    }


    /**
     * Restore a database table
     *
     * @return bool
     */
    public static function restore()
    {
        $file = new FileHandler(storage_dir() . 'backups/' . static::$table . ".json", 'r');
        $result = null;
        foreach (json_decode($file->read()) as $data) {
            if (self::insert((array)$data)) {
                $result = true;
            } else {
                $result = false;
                break;
            }
        }
        return ($result);
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
                return trigger_error("insert() expects one parameter, an array of key and value pairs.", E_USER_ERROR);
            }
            $fields = trim(Formatter::arrayConcat(array_keys($valuePairs), ","), ',');
            $values = "";

            /**
             * compose all question marks for a prepared statement
             * that corresponds to number of key and value.
             */
            for ($i = 0; $i < count($valuePairs); $i++) {
                $values .= '?,';
            }

            $query = "INSERT INTO " . static::$table . "({$fields}) VALUES(" . trim($values, ',') . ")";
            $stmt = self::getInstance()->prepare($query);

            return $stmt->execute(array_values($valuePairs));
        } catch (PDOException $e) {
            print $e->getMessage();
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
    public function save()
    {
        if (self::update($this->fields, $this->original->id)) {
            foreach ($this->fields as $field => $value) {
                if (property_exists($this->original, $field)) {
                    $this->original->$field = self::find($this->original->id)->pull($field);
                }
            }
        }
        $this->fields = [];
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
     * @method delete($id = null)
     * @param null $id
     * @return mixed
     */
    public static function delete($id = null)
    {
        try {
            self::$table = static::$table;

            /**
             * if delete is chained from first() and find()
             * method, append the delete keyword on $query
             */
            if (is_null($id) && empty(self::$result)) {
                self::$query['select'] = 'DELETE ';
                $stmt = self::getInstance()->prepare(self::parseQuery());
                $stmt = $stmt->execute(self::$values);
                self::$values = [];
                self::$query = [];

                return ($stmt);
            } else {
                /**
                 * when query string is on process,
                 * prepend a delete keyword and execute.
                 */
                if (!empty(self::$query)) {
                    self::$query['select'] = 'DELETE';
                    $stmt = self::getInstance()->prepare(self::parseQuery());
                    $result = $stmt->execute(array_values(self::$values));
                    self::$query = [];
                    self::$values = [];
                    return ($result);
                } else {
                    $id = (!is_null($id)) ? $id : self::$result->id;
                }
            }

            /**
             * If multiple arguments are passed in,
             * handle them recursively.
             */
            if (count(func_get_args()) > 1) {
                foreach (func_get_args() as $arg) {
                    $stmt = self::getInstance()->exec("DELETE FROM " . static::$table . " WHERE id={$arg};");
                }
            } else {
                /**
                 * if only single parameter is given,
                 * or is the $result object is set,
                 * get that id and delete.
                 */
                $stmt = self::getInstance()->exec("DELETE FROM " . static::$table . " WHERE id={$id};");
            }
            return ($stmt);
        } catch (PDOException $e) {
            print $e->getMessage();
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
        /**
         * if multiple arguments supplied, concatenate
         * them and store in $query
         */
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
        self::$table = static::$table;

        if (!is_null($b)) {
            $param = "{$a} ?";
            self::$values[] = $b;
        } else {
            $param = "= ?";
            self::$values[] = $a;
        }

        if (isset(self::$query['where']) && preg_match('/WHERE/i', self::$query['where'])) {
            self::$query['where'] = self::$query['where'] . " AND {$field} {$param}";
        } else {
            self::$query['where'] = "WHERE {$field} {$param}";
        }
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
        self::$table = static::$table;
        $params = "";
        foreach ($values as $value) {
            $params .= "?,";
            self::$values[] = $value;
        }

        $params = trim($params, ',');

        if (isset(self::$query['where']) && preg_match('/WHERE/i', self::$query['where'])) {
            self::$query['where'] = self::$query['where'] . " AND {$column} IN ({$params})";
        } else {
            self::$query['where'] = "WHERE {$column} IN ({$params})";
        }

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
        self::$table = static::$table;
        self::$values[] = $first;
        self::$values[] = $second;

        if (isset(self::$query['where']) && preg_match('/WHERE/i', self::$query['where'])) {
            self::$query['where'] = self::$query['where'] . " AND {$column} BETWEEN ? AND ?";
        } else {
            self::$query['where'] = "WHERE {$column} BETWEEN ? AND ?";
        }
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
        self::$table = static::$table;
        self::$values[] = $first;
        self::$values[] = $second;

        if (isset(self::$query['where']) && preg_match('/WHERE/i', self::$query['where'])) {
            self::$query['where'] = self::$query['where'] . " OR {$column} BETWEEN ? AND ?";
        } else {
            return trigger_error("orWhereBetween() should be called next to where() method.", E_USER_ERROR);
        }
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
        self::$table = static::$table;

        if (!is_null($b)) {
            $param = "{$a} ?";
            self::$values[] = $b;
        } else {
            $param = "= ?";
            self::$values[] = $a;
        }

        if (isset(self::$query['where']) && preg_match('/WHERE/i', self::$query['where'])) {
            self::$query['where'] = self::$query['where'] . " OR {$field} {$param}";
        } else {
            return trigger_error("orWhere() should be called next to where() method.", E_USER_ERROR);
        }
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
        if (isset($order)) {
            if (!preg_match('/DESC/i', $order) && !preg_match('/ASC/i', $order)) {
                return trigger_error("Second argument passed in orderBy() method should be 'ASC' or 'DESC'", E_USER_ERROR);
            }
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
            return trigger_error("Argument passed in limit() method should be numeric.", E_USER_ERROR);
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
     * find a single row from database using the given id
     * Fetch the single row from database and set $result
     * from it, return new instance of class itself along with
     * its preserved properties for it
     * to be able to use all the methods and for method chaining
     * like User::first()->delete()
     * Note: you cannot store new instance of this class in a
     * session variable. refer to __invoke or use data() method
     * to get the original values.
     *
     * @param $id
     * @return self|bool
     * @throws InvalidMethodCallException
     */
    public static function find($id)
    {
        try {
            self::$table = static::$table;
            $stmt = self::getInstance()->prepare("SELECT * FROM " . static::$table . " WHERE id=? LIMIT 1");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            if ($result) {
                self::$result = $result;
                return new self($result);
            } else {
                return (null);
            }
        } catch (PDOException $e) {
            print $e->getMessage();
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
            if (!empty(self::$result)) {
                if (property_exists(self::$result, $column)) {
                    return (self::$result->$column);
                } else {
                    return (null);
                }
            } else {
                $stmt = self::getInstance()->prepare(self::parseQuery());
                $stmt->execute(array_values(self::$values));
                $result = $stmt->fetch(PDO::FETCH_OBJ);

                if (!$result) {
                    return (false);
                } else {
                    if (property_exists($result, $column)) {
                        return ($result->$column);
                    } else {
                        return (null);
                    }
                }
            }
        } catch (PDOException $e) {
            print $e->getMessage();
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
            return ($stmt->rowCount() == 0) ? false : true;
        } catch (PDOException $e) {
            print $e->getMessage();
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
            $stmt = self::getInstance()->prepare("SELECT * FROM " . static::$table . " ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            if ($result) {
                return new self($result);
            } else {
                return (null);
            }
        } catch (PDOException $e) {
            print $e->getMessage();
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
            $stmt = self::getInstance()->prepare("SELECT * FROM " . static::$table . " ORDER BY id ASC LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            if ($result) {
                return new self($result);
            } else {
                return (null);
            }
        } catch (PDOException $e) {
            print $e->getMessage();
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
            print $e->getMessage();
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
            print $e->getMessage();
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
     * Fetch the single row from database and set $result
     * from it, return new instance of class itself along with
     * its preserved properties for it
     * to be able to use all the methods and for method chaining
     * like User::first()->delete()
     * Note: you cannot store new instance of this class in a
     * session variable. refer to __invoke or use data() method
     * to get the original values.
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
            $result = $stmt->fetch(PDO::FETCH_OBJ);

            if ($result) {
                return new self($result);
            } else {
                return (null);
            }
        } catch (PDOException $e) {
            print $e->getMessage();
        }
    }


    /**
     * Increment a value, assuming the field's
     * default value is integer.
     *
     * @param $field
     * @param null $int
     * @var int $value
     * @var bool $stmt
     * @return self
     */
    public static function increment($field, $int = null)
    {
        try {
            if (!empty(self::$result)) {
                if (property_exists(self::$result, $field)) {
                    if (!is_numeric(self::$result->$field)) {
                        return trigger_error("Field '$field' is not numeric, cannot do an increment.", E_USER_ERROR);
                    }
                } else {
                    return trigger_error("Field '$field' does not exist in " . self::$table . " table", E_USER_ERROR);
                }

                $value = is_null($int) ? self::$result->$field + 1 : self::$result->$field + $int;
                $stmt = self::getInstance()->exec("UPDATE " . self::$table . " SET {$field}={$value} WHERE id=" . self::$result->id);
                return ($stmt);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            print $e->getMessage();
        }
    }


    /**
     * Decrement a value, assuming the field's
     * default value is integer.
     *
     * @param $field
     * @param null $int
     * @var int $value
     * @var bool $stmt
     * @return self
     */
    public static function decrement($field, $int = null)
    {
        try {
            if (!empty(self::$result)) {
                if (property_exists(self::$result, $field)) {
                    if (!is_numeric(self::$result->$field)) {
                        return trigger_error("Field '$field' is not numeric, cannot do a decrement.", E_USER_ERROR);
                    }
                } else {
                    return trigger_error("Field '$field' does not exist in " . self::$table . " table", E_USER_ERROR);
                }

                $value = is_null($int) ? self::$result->$field - 1 : self::$result->$field - $int;
                $stmt = self::getInstance()->exec("UPDATE " . self::$table . " SET {$field}={$value} WHERE id=" . self::$result->id);
                return ($stmt);
            } else {
                return false;
            }
        } catch (PDOException $e) {
            print $e->getMessage();
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
            print $e->getMessage();
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
    private static function parseQuery()
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
     * Returns original object generated
     * from first() and find() method.
     *
     * @return string
     */
    public static function data()
    {
        if (!empty(self::$result)) {
            return self::$result;
        } else {
            self::first();
            return self::$result;
        }
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
            $resource = !empty(self::$result) ? [(array)self::$result] : self::get(PDO::FETCH_ASSOC);
            $values = "";

            foreach ($resource as $result) {
                $values .= trim(Formatter::arrayConcat($result, $delimiter), $delimiter) . "{$separator}";
            }

            return trim($values, '|');
        } catch (PDOException $e) {
            print $e->getMessage();
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
            if (!empty(self::$result)) {
                return json_encode(self::$result);
            }
            return json_encode(self::get(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            print $e->getMessage();
        }
    }
}