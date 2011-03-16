<?php

class Joiner_Sqlite_Adapter extends Joiner_Adapter {

    protected $tableClass = 'Joiner_Sqlite_Table';

    function getTable($tableExpr) {

        require_once Joiner::$path . '/Table.php';
        require_once Joiner::$path . '/Sqlite/Table.php';

        return parent::getTable($tableExpr);

    }

}