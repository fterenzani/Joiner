<?php

abstract class Joiner {

    static $path = '';

    static protected
            $adapters = array(),
            $current
    ;

    static function setAdapter($name, $dsn, $username = NULL, $password = NULL, $options = array()) {

        // First call, set up the default adapter and the current path
        if (!isset(self::$current)) {

            self::$path = firname(__FILE__);
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

    static function setCurrentAdapter($name) {

        self::$current = $name;

    }


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

    function getSchema() {
        if (!isset($this->schema)) {
            require_once Joiner::$path . '/Schema.php';
            $this->schema = new Joiner_Schema;
        }
        return $this->schema;
    }

    function execute($sql, $params = array()) {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute((array) $params);
        return $stmt;
    }

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

    function expr($expr, $params = array()) {
        require_once Joiner::$path . '/Expression.php';
        return new Joiner_Expression($expr, $params);

    }

    function  __call($method, $args) {
        return call_user_func_array(array($this->getConnection(), $method), $args);
    }
}

class Joiner_Sqlite_Adapter extends Joiner_Connector {
    function getTable($tableExpr) {
        $table = $this->getSchema()->resolveTableExpr($tableExpr);
        require_once Joiner::$path . '/Sqlite/Table.php';
        return new Joiner_Sqlite_Table($this, $table['name'], $table['table'], $table['as']);
    }

}

class Joiner_Schema {

    private $tables = array();
    private $rels = array();


    function setTable($table, $name = NULL) {
        if (!$name) {
            $name = $table;
        }

        $this->tables[$name] = $table;
        return $this;

    }

    function setRel($relOne, $relTwo) {

        $parts = explode('.', $relOne);
        $table1['name'] = $parts[0];
        $table1['table'] = $this->getTableByName($parts[0]);
        $table1['key'] = $parts[1];

        $parts = explode('.', $relTwo);
        $table2['name'] = $parts[0];
        $table2['table'] = $this->getTableByName($parts[0]);
        $table2['key'] = $parts[1];

        $this->rels[$table1['name']][$table2['name']]['key'] = $table1['key'];
        $this->rels[$table1['name']][$table2['name']]['foreign'] = $table2;

        $this->rels[$table2['name']][$table1['name']]['key'] = $table2['key'];
        $this->rels[$table2['name']][$table1['name']]['foreign'] = $table1;

        return $this;

    }


    function setCrossRef($nameOne, $between, $nameTwo) {

        $this->rels[$nameOne][$nameTwo]['ref'] = $between;
        $this->rels[$nameTwo][$nameOne]['ref'] = $between;

        return $this;

    }

    function getTableByName($name) {
        return isset($this->tables[$name])? $this->tables[$name]: $name;
    }

    function getRel($nameOne, $nameTwo) {

        if (isset($this->rels[$nameOne][$nameTwo])) {
            return $this->rels[$nameOne][$nameTwo];

        }

        throw new Exception("No relations between <i>$nameOne</i> and <i>$nameTwo</i> in this schema");

    }

    /**
     * $indetifier (table_name asName, internal_name asName, table_name, internal_name)
     */
    function resolveTableExpr($tableExpr) {
        if (preg_match('#(?:([a-z0-9_]+)\s+)?([a-z0-9_]+)#i', $tableExpr, $parts)) {

            $id = array();

            $id['as'] = $parts[2];

            if ($parts[1]) {
                $id['name'] = $parts[1];

            } else {
                $id['name'] = $parts[2];

            }

            $id['table'] = $this->getTableByName($id['name']);

            return $id;

        }
        throw new Exception("Invalid identifier \"$tableExpr\". Valid identifiers are table_name, internal_name, table_name asName, internal_name asName");
    }

}

abstract class Joiner_TableDataAccess implements Iterator, ArrayAccess, Countable {
    protected $results;
    protected $stmt;
    protected $model;

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
        if (class_exists($this->model)) {
            return $this->model;
        }
        require_once Joiner::$path . '/Model.php';
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

class Joiner_Table extends Joiner_TableDataAccess {

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


    function __construct($db, $name, $table, $as) {
        $this->db = $db;
        $this->name = $name;
        $this->table = $table;
        $this->as = $as;

        $this->select = "$as.*";

        $this->useModel($name);

    }

    function  __toString() {
        $this->table . ' AS ' . $this->as;
    }

    function getDb() {
        return $this->db;
    }

    function join($tableExpr) {

        return $this->_join($tableExpr, 'INNER');

    }

    function leftJoin($tableExpr) {

        return $this->_join($tableExpr, 'LEFT');

    }

    protected function _join($tableExpr, $type) {

        $join = $this->db->getSchema()->resolveTableExpr($tableExpr);
        $rel = $this->db->getSchema()->getRel($this->name, $join['name']);

        if (isset($rel['foreign'])) {
            $this->join .= " $type JOIN $join[table] AS $join[as] ON $this->as.$rel[key] = $join[as].{$rel['foreign']['key']} ";

        } else {
            $crossRef = $rel['ref'];
            $rel = $this->db->getSchema()->getRel($this->name, $crossRef);
            $this->join .= " $type JOIN {$rel['foreign']['table']} AS {$rel['foreign']['name']} ON $this->as.$rel[key] = {$rel['foreign']['name']}.{$rel['foreign']['key']} ";


            $rel = $this->db->getSchema()->getRel($crossRef, $join['name']);
            $this->join .= " $type JOIN  $join[table] AS $join[as] ON $crossRef.$rel[key] = $join[as].{$rel['foreign']['key']} ";


        }

        return $this;

    }


    function where($where = null, $params = array()) {

        $this->where = $where? ' WHERE ' . $where: null;
        $this->params = array();

        return $this->bind($params);
        ;
    }

    function orWhere($where, $params = array()) {
        $this->where .= ($this->where? ' OR ': ' WHERE ') . $where;
        return $this->bind($params);
    }

    function andWhere($where, $params = array()) {
        $this->where .= ($this->where? ' AND ': ' WHERE ') . $where;
        return $this->bind($params);
    }

    function whereIn($field = null, $params = array()) {
        if ($field) {
            $str = ltrim(str_repeat(',?', count($params)), ',');
            $this->where .= ' WHERE ' . "$field IN ($str)";

        } else {
            $this->where = '';

        }
        $this->params = array();

        return $this->bind($params);
    }

    function andWhereIn($field, $params) {
        $str = ltrim(str_repeat(',?', count($params)), ',');
        $this->where .= ($this->where? ' AND ': ' WHERE ') . "$field IN ($str)";
        return $this->bind($params);
    }

    function orWhereIn($field, $params) {
        $str = ltrim(str_repeat(',?', count($params)), ',');
        $this->where .= ($this->where? ' OR ': ' WHERE ') . "$field ($str)";
        return $this->bind($params);
    }

    function bind($params) {
        foreach ((array) $params as $value) {
            $this->params[] = $value;
        }
        return $this;
    }


    function groupBy($field = null) {
        $this->groupBy = $field? ' GROUP BY ' . $group: null;
        return $this;
    }

    function addGroupBy($group) {
        $this->groupBy .= ($this->groupBy? ', ': ' GROUP BY ') . $group;
        return $this;
    }

    // refactor

    function orderBy($order = null) {
        $this->orderBy = $order? ' ORDER BY ' . $order: null;
        return $this;
    }

    function addOrderBy($order) {
        $this->orderBy .= (isset($this->orderBy)? ', ': ' ORDER BY ') . $order;
        return $this;
    }

    function limit($limit = NULL) {
        $this->limit = $limit;
        return $this;
    }

    function offset($offset = NULL) {
        $this->offset = $offset;
        return $this;
    }

    function setPage($page) {
        if (!$this->limit) {
            $this->limit = 1000;
        }
        $this->offset = $this->limit * (int)$page - $this->limit;
        return $this;

    }

    function having($having = null) {
        $this->having = ($having)? ' HAVING ' . $having: null;
        return $this;
    }

    function andHaving($having) {
        $this->having .= (isset($this->having)? ' AND HAVING ': ' HAVING ') . $having;
        return $this;
    }

    function orHaving($having) {
        $this->having .= (isset($this->having)? ' OR HAVING ': ' HAVING ') . $having;
        return $this;
    }

    function select($fields) {
        $this->select = $fields;
        return $this;
    }

    function addSelect($fields) {
        $this->select .= ($this->select? ', ': '') . $fields;
        return $this;

    }

    function distinct($bool = true) {
        $this->distinct = $bool? ' DISTINCT ': '';
        return $this;

    }

    function set(array $hash = array()) {
        $this->set = $hash;
        return $this;

    }

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
        throw new Exception("Not implemented");
    }


}


class Joiner_Sqlite_Table extends Joiner_Table {

    function toSqlUpdate() {

        if ($this->orderBy || $this->limit || $this->offset) {
            $where = ' WHERE rowid IN('
                    . "SELECT rowid FROM $this->table AS $this->as $this->where"
                    . $this->orderBy . ($this->limit? ' LIMIT ' . $this->limit: null)
                    . ($this->offset? ' OFFSET ' . $this->offset: null)
                    . ')';

        } else {
            $where = $this->where;

        }

        return 'UPDATE ' . $this->table
                . ' SET ' . $this->_parseSet() . $where;
    }

    function toSqlDelete() {

        if ($this->orderBy || $this->limit || $this->offset) {
            $where = ' WHERE rowid IN('
                    . "SELECT rowid FROM $this->table AS $this->as $this->where"
                    . $this->orderBy . ($this->limit? ' LIMIT ' . $this->limit: null)
                    . ($this->offset? ' OFFSET ' . $this->offset: null)
                    . ')';

        } else {
            $where = $this->where;

        }

        return 'DELETE FROM ' . $this->table . $where;

    }


    function getMetas() {
        $stmt = $this->db->query("PRAGMA table_info(" . $this->table . ")");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class Joiner_Mysql_Table {
    function getMetas() {
        $stmt = $this->db->query("SHOW COLUMNS FROM table [LIKE '" . $this->table . "'])");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}




/*
function __get($name) {
  if (method_exists($this, 'get_' . $name)) {
    return $this->{$name}();
  }
}
*/