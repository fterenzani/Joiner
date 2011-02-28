<?php

/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 */

/**
 * Joiner Expression
 *
 * <code>
 * $expr1 = Joiner::getAdapter()->expr('md5(?)', 'p4ssword');
 * $expr2 = Joiner::getAdapter()->expr('now()');
 *
 * Joiner::getAdapter()->getTable('User')
 *   ->set(array(
 *     'email' => 'foo@bar.it',
 *     'password' => $expr1,
 *     'created_at' => $expr2,
 *   ))
 *   ->insert();
 * </code>
 *
 * @package Joiner
 */

class Joiner_Expression {
    public $expr;
    public $params;
    
    function __construct($expr, $params) {
        $this->expr = $expr;
        $this->params = $params;

    }

    function __toString() {
        return $this->expr;
    }

}