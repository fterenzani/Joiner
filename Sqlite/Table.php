<?php

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

