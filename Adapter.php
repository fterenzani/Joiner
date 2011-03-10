<?php
/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 */

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

    protected $isLoggingEnabled = false;

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

    function getLog() {
        if ($this->pdo instanceof Joiner_PDOLogger) {
            return $this->pdo;
        }
        return null;

    }

    function  __call($method, $args) {
        return call_user_func_array(array($this->getConnection(), $method), $args);
    }
}