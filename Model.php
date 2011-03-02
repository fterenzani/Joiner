<?php
/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 */


/**
 * Joiner Model
 *
 * Used to hydrate a record.
 *
 * <code>
 * $record = Joiner::getAdapter()->getTable('Item');
 * $record[0] instanceOf Joiner_Model // true
 * </code>
 *
 * Field values can be accessed as array keys or object's properties.
 *
 * Extend this class if you want to add some methods ta a given record:
 *
 * <code>
 * class Item extends Joiner_Model {}
 *
 * $record = Joiner::getAdapter()->getTable('Item');
 * $record[0] instanceOf Joiner_Model // true
 * $record[0] instanceOf Item // true
 * </code>
 *
 * @package Joiner
 */

class Joiner_Model implements ArrayAccess {

    /**
     * @var string The alias of the table defined in Joiner_Schema or the table
     * name
     *
     * @todo rename it in alias
     */
    protected $__model;

    /**
     * @var object the adapter instance
     *
     * @todo rename it in adapter
     */
    protected $__db;

    function __construct($__model, $__db) {
        $this->__model = $__model;
        $this->__db = $__db;

    }

    /**
     * Get a copy of the record as array
     * @return array
     */
    function toArray() {
        $copy = clone $this;
        unset($copy->__model, $copy->__db);
        return (array) $copy;
    }

    /**
     * Get a table filtered by the relation with the current record. The
     * relation must be previously defined in Joiner_Schema.
     *
     * @param string $name The previously defined alias of the table in
     * Joiner_Schema
     *
     * @return Joiner_Table
     */
    function getRelated($name) {

        $related = $this->__db->getSchema()->resolveTableExpr($name);
        $rel = $this->__db->getSchema()
                ->getRelation($this->__model, $related['name']);

        if (isset($rel['ref'])) {

            $ref = $this->__db->getSchema()
                    ->getRelation($this->__model, $rel['ref']);

            return $this->__db->getTable($name)->join($rel['ref'])
                    ->where("{$ref['foreign']['name']}.{$ref['foreign']['key']} = ?", $this->{$ref['key']});
        }

        return $this->__db->getTable($name)
                ->where("$related[as].{$rel['foreign']['key']} = ?", $this->{$rel['key']});
    }

   
    // Array access

    function offsetExists($key) {
        return isset($this->{$key});
    }

    function offsetGet($key) {
        return isset($this->{$key})? $this->{$key}: null;

    }

    function offsetSet($key, $value) {
        $this->{$key} = $value;

    }
    function offsetUnset($key) {
        unset ($this->{$key});

    }

}