<?php
/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 */

/**
 * @package Joiner
 */

class Joiner_PDOStatementLogger {

    protected $stmt;

    function __construct(PDOStatement $stmt) {

        $this->stmt = $stmt;


    }

    function __call($method, $args) {
        $start = microtime(true);

        $return = call_user_func_array(array($this->stmt, $method), $args);

        Joiner_PDOLogger::$log[] = array('PDOStatement::' . $method, $args, microtime(true) - $start);

        return $return;

    }

}