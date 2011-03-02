<?php
/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 */

/**
 * Joiner
 *
 * This class provide the main interface to work with Joiner_Adapter instances.
 *
 * @package Joiner
 */
abstract class Joiner {

    /**
     * @var string The current path used to load libraries
     */
    static $path = '';

    /**
     * @var array A map of Joiner_Adapter instances by name.
     */
    static protected $adapters = array();

    /**
     * @var string The current key of the Joiner::$adapters map
     */
    static protected $current;

    /**
     * Set up, strore and return a new Joiner_Adapter instance
     *
     * @param string $name The name of the new Joiner_Adapter instance
     * @param string $dsn A PDO dsn
     * @param string $username
     * @param string $password
     * @param string $options PDO options
     * @return Joiner_Adapter
     */
    static function setAdapter($name, $dsn, $username = NULL, $password = NULL, $options = array()) {

        // First call, set up the default adapter and the current path
        if (!isset(self::$current)) {

            self::$path = dirname(__FILE__);
            self::$current = $name;

        }

        // Load the driver's Adapter or use the generic one
        preg_match('#^[^:]+#', $dsn, $driver);
        $driver = ucfirst($driver[0]);

        if (file_exists(self::$path . '/' . $driver . '/Adapter.php')) {

            require_once self::$path . '/' . $driver . '/Adapter.php';
            $class = 'Joiner_' . ucfirst($driver[0]) . '_Adapter';

        } else {

            $class = 'Joiner_Adapter';

        }
        $adapter = new $class($dsn, $username, $password, $options);

        // Store the adapter
        self::$adapters[$name] = $adapter;

        return $adapter;


    }

    /**
     * Set the current adapter. The current adapter is the one that is returned
     * when Joiner::getAdapter() is called without the first parameter.
     *
     * @param string $name An adapter name
     */
    static function setCurrentAdapter($name) {

        self::$current = $name;

    }


    /**
     * Get a previously defined adapter by name. If the name is omitted, it
     * return the current adapter.
     *
     * @param string $name An adapter name
     * @throws Exception if the name don't match
     * @return Joiner_Adapter
     */
    static function getAdapter($name = NULL) {

        if (!$name)
            $name = self::$current;

        if (isset(self::$adapters[$name])) {
            return self::$adapters[$name];

        } else {
            throw new Exception("No adapter \"$name\" found");

        }

    }

}

/**
 * Joiner_Adapter
 * @package Joiner
 */
class Joiner_Adapter extends Joiner {
    protected $pdo;
    protected $dsn;
    protected $username;
    protected $password;
    protected $options;

    protected $schema;

    function __construct($dsn, $username= null, $password = null, $options = array()) {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options;

    }

    /**
     * @return PDO This per adapter PDO instance
     */
    function getConnection() {
        if (!isset($this->pdo)) {
            $this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // let's hide this informations
            unset($this->dsn);
            unset($this->username);
            unset($this->password);
            unset($this->options);
        }
        return $this->pdo;
    }

    /**
     * @return Joiner_Schema
     */
    function getSchema() {
        if (!isset($this->schema)) {
            require_once Joiner::$path . '/Schema.php';
            $this->schema = new Joiner_Schema;
        }
        return $this->schema;
    }

    /**
     * Prepare, bind the params and execute a query.
     * @param string $sql
     * @param mixed $params
     * @return PDOStatement
     */
    function execute($sql, $params = array()) {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute((array) $params);
        return $stmt;
    }

    /**
     * @param string $tableExpr Valid expressions are table_name, TableAlias, table_name as_table, TableAlias as_table
     * @return Joiner_Table 
     */
    function getTable($tableExpr) {
        $table = $this->getSchema()->resolveTableExpr($tableExpr);

        require_once Joiner::$path . '/Table.php';

        return new Joiner_Table($this, $table['name'], $table['table'], $table['as']);

    }

    // Don't respect the PDO interface
    function prepare($sql) {
        return $this->getConnection()->prepare($sql);
    }

    // Don't respect the PDO interface
    function exec($sql) {
        return $this->getConnection()->exec($sql);
    }

    // Don't respect the PDO interface
    function query($sql) {
        return $this->getConnection()->query($sql);
    }

    // Need test
    function findBy($string, $value) {
        $string = explode('.', $string);
        if (count($string) != 2) {
            throw new Exception('First argument is not valid');
        }
        return $this->getTable($string[0])->where("$string[1] = ?", array($value))->fetchAll();
    }

    // Need test
    function findOneBy($string, $value) {
        $string = explode('.', $string);
        if (count($string) != 2) {
            throw new Exception('First argument is not valid');
        }
        return $this->getTable($string[0])->where("$string[1] = ?", array($value))->limit(1)->fetch();
    }


    function getExpr($expr, $params = array()) {
        require_once Joiner::$path . '/Expression.php';
        return new Joiner_Expression($expr, $params);

    }

    function  __call($method, $args) {
        return call_user_func_array(array($this->getConnection(), $method), $args);
    }
}