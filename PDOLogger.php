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
    public static $log = array();

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

            if ($log[0] === 'PDOStatement::setFetchMode')
                continue;

            $milliseconds = round($log[2] * 1000);
            $return .= "<tr><td valign=top>$log[0]<td>";

			foreach ($log[1] as $i => $param) {
              $return .= 'Param ' . ($i + 1) . ":\n<pre style='white-space:pre-wrap'>" . print_r($param, true) . "</pre>\n<br>";

            }

			$return .= "<td>$milliseconds";
            $total += $milliseconds;
        }

        return $return . '<tr><th colspan=2>Milliseconds:<td>' . $total . '</table>';

    }
}