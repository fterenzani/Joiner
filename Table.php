<?php
/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 */

/**
 * Joiner_Table
 *
 * <code>
 * Joiner::getAdapter()->getTable('TableName') instanceOf Joiner_Table // true
 * </code>
 */
class Joiner_Table implements Iterator, ArrayAccess, Countable {

    protected $db;

    public $name;
    public $table;
    public $as;

    protected $join;

    protected $where;
    protected $params = array();

    protected $groupBy;

    protected $having;

    protected $orderBy;

    protected $limit;

    protected $offset;

    protected $select;
    protected $distinct;

    protected $set = array();

    protected $results;
    protected $stmt;
    protected $model;


    function __construct($db, $name, $table, $as) {
        $this->db = $db;
        $this->name = $name;
        $this->table = $table;
        $this->as = $as;

        $this->select = "$as.*";

        $this->useModel($name);

    }


    /**
     * @return Joiner_Adapter
     */
    function getAdapter() {
        return $this->db;
    }

    /**
     * @param string $tableExpr
     * @return Join_Table
     */
    function join($tableExpr) {

        return $this->_join($tableExpr, 'INNER');

    }

    /**
     * @param string $tableExpr
     * @return Join_Table
     */
    function leftJoin($tableExpr) {

        return $this->_join($tableExpr, 'LEFT');

    }

    /**
     * @param string $tableExpr
     * @param string $type
     * @return Joiner_Table
     */
    protected function _join($tableExpr, $type) {

        $join = $this->db->getSchema()->resolveTableExpr($tableExpr);
        $rel = $this->db->getSchema()->getRelation($this->name, $join['name']);

        if (isset($rel['foreign'])) {
            $this->join .= " $type JOIN $join[table] AS $join[as] ON $this->as.$rel[key] = $join[as].{$rel['foreign']['key']} ";

        } else {
            $crossRef = $rel['ref'];
            $rel = $this->db->getSchema()->getRelation($this->name, $crossRef);
            $this->join .= " $type JOIN {$rel['foreign']['table']} AS {$rel['foreign']['name']} ON $this->as.$rel[key] = {$rel['foreign']['name']}.{$rel['foreign']['key']} ";


            $rel = $this->db->getSchema()->getRelation($crossRef, $join['name']);
            $this->join .= " $type JOIN  $join[table] AS $join[as] ON $crossRef.$rel[key] = $join[as].{$rel['foreign']['key']} ";

        }

        return $this;

    }

    /**
     * @param string $where
     * @param mixin $params
     * @return Joiner_Table
     */
    function where($where = null, $params = array()) {

        $this->where = $where? ' WHERE (' . $where . ')': null;
        $this->params = array();

        return $this->bind($params);
    }

    /**
     * @param string $where
     * @param mixin $params
     * @return Joiner_Table
     */
    function orWhere($where, $params = array()) {
        $this->where .= ($this->where? ' OR (': ' WHERE (') . $where . ')';
        return $this->bind($params);
    }

    /**
     * @param string $where
     * @param mixin $params
     * @return Joiner_Table
     */
    function andWhere($where, $params = array()) {
        $this->where .= ($this->where? ' AND (': ' WHERE (') . $where . ')';
        return $this->bind($params);
    }


    /**
     * @param string $field
     * @param array $params
     * @return Joiner_Table
     */
    function whereIn($field = null, Array $params = array()) {
        if ($field) {
            $str = ltrim(str_repeat(',?', count($params)), ',');
            $this->where .= ' WHERE ' . "($field IN ($str))";

        } else {
            $this->where = '';

        }
        $this->params = array();

        return $this->bind($params);
    }

    /**
     * @param string $field
     * @param array $params
     * @return Joiner_Table
     */
    function andWhereIn($field, $params) {
        $str = ltrim(str_repeat(',?', count($params)), ',');
        $this->where .= ($this->where? ' AND (': ' WHERE (') . "$field IN ($str))";
        return $this->bind($params);
    }

    /**
     * @param string $field
     * @param array $params
     * @return Joiner_Table
     */
    function orWhereIn($field, $params) {
        $str = ltrim(str_repeat(',?', count($params)), ',');
        $this->where .= ($this->where? ' OR (': ' WHERE (') . "$field ($str))";
        return $this->bind($params);
    }

    /**
     * @param mixin $params
     * @return Joiner_Table
     */
    function bind($params) {
        foreach ((array) $params as $value) {
            $this->params[] = $value;
        }
        return $this;
    }

    /**
     * @param string $group
     * @return Joiner_Table
     */
    function groupBy($group) {
        $this->groupBy = $group? ' GROUP BY ' . $group: null;
        return $this;
    }

    /**
     * @param string $group
     * @return Joiner_Table
     */
    function addGroupBy($group) {
        $this->groupBy .= ($this->groupBy? ', ': ' GROUP BY ') . $group;
        return $this;
    }


    /**
     * @param string $order
     * @return Joiner_Table
     */
    function orderBy($order = null) {
        $this->orderBy = $order? ' ORDER BY ' . $order: null;
        return $this;
    }

    /**
     * @param string $order
     * @return Joiner_Table
     */
    function addOrderBy($order) {
        $this->orderBy .= (isset($this->orderBy)? ', ': ' ORDER BY ') . $order;
        return $this;
    }

    /**
     * @param int $limit
     * @return Joiner_Table
     */
    function limit($limit = NULL) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return Joiner_Table
     */
    function offset($offset = NULL) {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @param string $having
     * @return Joiner_Table
     */
    function having($having = null) {
        $this->having = ($having)? ' HAVING ' . $having: null;
        return $this;
    }

    /**
     * @param string $having
     * @return Joiner_Table
     */
    function andHaving($having) {
        $this->having .= (isset($this->having)? ' AND HAVING ': ' HAVING ') . $having;
        return $this;
    }

    /**
     * @param string $having
     * @return Joiner_Table
     */
    function orHaving($having) {
        $this->having .= (isset($this->having)? ' OR HAVING ': ' HAVING ') . $having;
        return $this;
    }

    /**
     * @param string $fields
     * @return Joiner_Table
     */
    function select($fields) {
        $this->select = $fields;
        return $this;
    }

    /**
     * @param string $fields
     * @return Joiner_Table
     */
    function addSelect($fields) {
        $this->select .= ($this->select? ', ': '') . $fields;
        return $this;

    }

    /**
     * @param bool $bool
     * @return Joiner_Table
     */
    function distinct($bool = true) {
        $this->distinct = $bool? ' DISTINCT ': '';
        return $this;

    }

    /**
     * @param array $hash
     * @return Joiner_Table
     */
    function set(array $hash = array()) {
        $this->set = $hash;
        return $this;

    }

    /**
     * @param array $hash
     * @return Joiner_Table
     */
    function mergeSet(array $hash) {
        $this->set = array_merge($this->set, $hash);
        return $this;

    }

    function toSqlSelect() {
        return 'SELECT ' . $this->distinct . $this->select
                . ' FROM ' . $this->table . ' AS ' . $this->as . $this->join
                . $this->where . $this->groupBy . $this->having
                . $this->orderBy . ($this->limit? ' LIMIT ' . $this->limit: null)
                . ($this->offset? ' OFFSET ' . $this->offset: null);

    }

    function toSqlSelectCount() {
        return 'SELECT COUNT(*) '
                . ' FROM ' . $this->table . ' AS ' . $this->as . $this->join
                . $this->where . $this->groupBy . $this->having;

    }

    function toSqlUpdate() {
        return 'UPDATE ' . $this->table . ' AS ' . $this->as
                . ' SET ' . $this->_parseSet() . $this->where
                . $this->orderBy . ($this->limit? ' LIMIT ' . $this->limit: null)
                . ($this->offset? ' OFFSET ' . $this->offset: null);
    }


    function _parseSet() {
        $this->setParams = array();
        $set = '';
        foreach ($this->set as $field => $value) {
            $set .= ", $field = ";
            if ($value instanceof Joiner_Expression) {
                $set .= $value;
                if ($value->params) {
                    $this->setParams = array_merge($this->setParams, $value->params);
                }
            } else {
                $set .= '?';
                $this->setParams[] = $value;
            }
        }

        if (!$set) {
            throw new Exception("Update error: no value to set");
        }

        return ltrim($set, ', ');

    }


    function toSqlDelete() {
        return 'DELETE FROM ' . $this->table . $this->where
                . $this->orderBy . ($this->limit? ' LIMIT ' . $this->limit: null)
                . ($this->offset? ' OFFSET ' . $this->offset: null);

    }

    function toSqlInsert() {
        return 'INSERT INTO ' . $this->table . '(' . implode(', ', array_keys($this->set)) . ') '
                . 'VALUES (' . $this->_parseValues() . ')';

    }

    function toSqlReplace() {
        return 'REPLACE INTO ' . $this->table . '(' . implode(', ', array_keys($this->set)) . ') '
                . 'VALUES (' . $this->_parseValues() . ')';

    }


    function _parseValues() {
        $this->setParams = array();
        $values = '';
        foreach ($this->set as $value) {
            if ($value instanceof Joiner_Expression) {
                $values .= ", $value";
                if ($value->params) {
                    $this->setParams = array_merge($this->setParams, $value->params);
                }
            } else {
                $values .= ', ?';
                $this->setParams[] = $value;
            }
        }

        if (!$values) {
            throw new Exception("Insert error: no value to insert");
        }

        return ltrim($values, ', ');

    }

    function truncate() {
        $this->db->exec("TRUNCATE TABLE $this->table");
    }

    function copy() {
        return clone $this;
    }

    function apply($callable) {
        if (is_string($callable)) {
            $callable($this);

        } else {
            call_user_func($callable, $this);

        }
        return $this;

    }

    function getMetas() {
        $stmt = $this->db->query("SHOW COLUMNS FROM table [LIKE '" . $this->table . "'])");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Iterator
     */

    function rewind() {
        $this->load();
        reset($this->results);
    }

    function current() {
        return current($this->results);

    }

    function key() {
        return key($this->results);

    }

    function next() {
        return next($this->results);
    }

    function valid() {
        return (bool) $this->current();
    }

    /**
     * Array access
     */

    function offsetExists($key) {
        $this->load();
        return isset($this->results[$key]);
    }

    function offsetGet($key) {
        $this->load();
        return isset($this->results[$key])? $this->results[$key]: null;

    }

    function offsetSet($key, $value) {

    }
    function offsetUnset($key) {

    }

    /**
     * Countable
     */
    function count() {
        $this->load();
        return count($this->results);
    }


    function load() {
        if (!isset($this->results)) {
            $this->results = $this->fetchAll();
            return true;
        }
        return false;
    }

    function unload() {
        unset($this->results);
        unset($this->stmt);
    }

    function fetch() {
        if (!isset($this->stmt)) {
            $this->stmt = $this->db->execute($this->toSqlSelect(), $this->params);
            $this->stmt->setFetchMode(PDO::FETCH_CLASS, $this->getModelClass(), array($this->model, $this->db));
        }
        $row = $this->stmt->fetch();
        if (!$row) {
            unset($this->stmt);
        }
        return $row;

    }

    function fetchAll() {
        $stmt = $this->db->execute($this->toSqlSelect(), $this->params);
        $stmt->setFetchMode(PDO::FETCH_CLASS, $this->getModelClass(), array($this->model, $this->db));

        return $stmt->fetchAll();
    }

    function fetchOne() {
        return $this->limit(1)->fetch();
    }


    function fetchColumn($column = null) {
        if (!isset($this->stmt)) {
            $this->stmt = $this->db->execute($this->toSqlSelect(), $this->params);
        }
        return $this->stmt->fetchColumn($column);
    }

    function fetchColumns($column = null) {
        $columns = array();
        while ($col = $this->fetchColumn($column))
            $columns[] = $col;

        return $columns;
    }

    function fetchCount() {
        return $this->db->execute($this->toSqlSelectCount(), $this->params)->fetchColumn();
    }

    function useModel($tableName) {
        $this->model = $tableName;
        return $this;
    }

    function getModelClass() {
        require_once Joiner::$path . '/Model.php';
        if (class_exists($this->model)) {
            return $this->model;
        }
        return 'Joiner_Model';
    }

    function insert(array $row = array()) {
        if ($row) {
            $this->set($row);
        }
        return $this->db->execute($this->toSqlInsert(), $this->setParams);
    }

    function replace(array $row = array()) {
        if ($row) {
            $this->set($row);
        }
        return $this->db->execute($this->toSqlReplace(), $this->setParams);
    }

    function update(array $row = array()) {
        if ($row) {
            $this->set($row);
        }
        return $this->db->execute($this->toSqlUpdate(), array_merge($this->setParams, $this->params));
    }

    function delete() {
        return $this->db->execute($this->toSqlDelete(), $this->params);
    }


}
