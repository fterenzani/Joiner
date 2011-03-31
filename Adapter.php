<?php
/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 */

/**
 * Joiner_Adapter
 * @package Joiner
 */
class Joiner_Adapter {
    protected $pdo;
    protected $dsn;
    protected $username;
    protected $password;
    protected $options;

    protected $schema;

    protected $isLoggingEnabled = false;
    
    protected $tableClass = 'Joiner_Table';

    function __construct($dsn, $username= null, $password = null, $options = array()) {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options;

    }

    /**
     * @return PDO|PDOLogger This per adapter PDO instance
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

            if ($this->isLoggingEnabled) {
                // decorate the pdo object
                $this->enableLogging(true);
            }
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
        $tableExpr = array_map('trim', explode(',', $tableExpr));

        $table = $this->getSchema()->resolveTableExpr(array_shift($tableExpr));

        require_once Joiner::$path . '/Table.php';

        $table = new $this->tableClass($this, $table['name'], $table['table'], $table['as']);
        
        foreach ($tableExpr as $expr) {
            $table->join($expr);

        }

        return $table;

    }

    /**
     * Proxy for PDO::prepare
     * @param string $sql An SQL statement to prepare
     * @param array $driver_options PDO driver options
     * @return PDOStatement
     */
    function prepare($sql, $driver_options = null) {
        return $this->getConnection()->prepare($sql, $driver_options);
    }

    /**
     * Proxy for PDO::exec
     * @param string $sql An SQL statement
     * @return int The number of affected rows
     */
    function exec($sql) {
        return $this->getConnection()->exec($sql);
    }

    /**
     * Proxy for PDO::query
     * @param string $sql Proxy for PDO::exec
     * @param int $fetchMode The PDO fetch mode
     * @param mixin $mixin1 Fetch mode specific argument
     * @param mixin $mixin2 Fetch mode specific argument
     * @return PDOStatement
     */
    function query($sql, $fetchMode = null, $mixin1 = null,  $mixin2 = null) {
        return $this->getConnection()->query($sql, $fetchMode, $mixin1, $mixin2);
    }

    /**
     *
     * @param string $string
     * @param string|number $value
     * @return Joiner_Table
     */
    function findBy($string, $value) {
        $string = explode('.', $string);
        if (count($string) != 2) {
            throw new Exception('First argument is not valid');
        }
        return $this->getTable($string[0])->where("$string[1] = ?", array($value))->fetchAll();
    }

   /**
    *
    * @param string $string
    * @param string|number $value
    * @return Joiner_Model|false
    */
    function findOneBy($string, $value) {
        $string = explode('.', $string);
        if (count($string) != 2) {
            throw new Exception('First argument is not valid');
        }
        return $this->getTable($string[0])->where("$string[1] = ?", array($value))->limit(1)->fetch();
    }

    /**
     *
     * @param string Sql expression ('now()', 'md5(?)', ...)
     * @param mixin $params The related param to bind
     * @return Joiner_Expression
     */
    function getExpr($expr, $params = array()) {
        require_once Joiner::$path . '/Expression.php';
        return new Joiner_Expression($expr, $params);

    }

    /**
     * Decorate the PDO object with a logger
     * @param bool $bool True for enable, false to disable
     * @return void
     */
    function enableLogging($bool = true) {
        $this->isLoggingEnabled = $bool;
        if (isset($this->pdo)) {
            if ($this->pdo instanceof PDO) {
                require_once Joiner::$path . '/PDOLogger.php';
                $this->pdo = new Joiner_PDOLogger($this->pdo);

            } else {
                $this->pdo = $this->pdo->getPdo();

            }
        }

    }

    /**
     * Return the Joiner_PDOLogger instance is loggin is enabled
     * @return null|Joiner_PDOLogger
     */
    function getLog() {
        if ($this->pdo instanceof Joiner_PDOLogger) {
            return $this->pdo;
        }
        return null;

    }

    /**
     * A proxy for the PDO methods not defined in thi class
     * @param string $method The PDO method to call
     * @param array $args The arguments of the given method
     * @return mixin
     */
    function  __call($method, $args) {
        return call_user_func_array(array($this->getConnection(), $method), $args);
    }
}