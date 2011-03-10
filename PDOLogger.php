<?php
/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 */

/**
 * @package Joiner
 */

class Joiner_PDOLogger {

    protected $pdo;
    public static $log;

    function __construct(PDO $pdo) {

        $this->pdo = $pdo;


    }

    function getPdo() {
        return $this->pdo;
    }


    function __call($method, $args) {
        $start = microtime(true);

        $return = call_user_func_array(array($this->pdo, $method), $args);

        self::$log[] = array('PDO::' . $method, $args, microtime(true) - $start);

        if ($return instanceOf PDOStatement) {
            require_once Joiner::$path . '/PDOStatementLogger.php';
            $return = new Joiner_PDOStatementLogger($return);
        }

        return $return;

    }

    function toArray() {
        return self::$log;
    }

    function __toString() {

        $total = 0;
        $return = '<table border=1><tr><th>Func<th>Args<th>Milliseconds';

        foreach (self::$log as $log) {
            $milliseconds = round($log[2] * 1000);
            $return .= "<tr><td>$log[0]<td>" . print_r($log[1], true) . "<td>$milliseconds";
            $total += $milliseconds;
        }

        return $return . '<tr><th colspan=2>Milliseconds:<td>' . $total . '</table>';
        
    }
}