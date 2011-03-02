<?php

class Joiner_Sqlite_Adapter extends Joiner_Connector {
    function getTable($tableExpr) {
        $table = $this->getSchema()->resolveTableExpr($tableExpr);
        require_once Joiner::$path . '/Sqlite/Table.php';
        return new Joiner_Sqlite_Table($this, $table['name'], $table['table'], $table['as']);
    }

}