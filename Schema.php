<?php
/**
 * @author Francesco Terenzani <f.terenzani@gmail.com>
 * @copyright Copyright (c) 2011, Francesco Terenzani
 */


/**
 * Joiner Schema
 *
 * Every adapter has its own Schema that is used to map tables and their
 * relations.
 *
 * Below the main usage of the Joiner Schema.
 *
 * <code>
 * $schema = Joiner::getAdapter()->getSchema();
 *
 * // Let's define some tables
 * $schema->setTable('blog_posts', 'BlogPost');
 * $schema->setTable('blog_tags', 'BlogTag');
 * $schema->setTable('blog_post_tag', 'BlogPostTag');
 *
 * // Let's define their relations
 * $schema->setRelation('BlogPostTag.post_id', 'BlogPost.id');
 * $schema->setRelation('BlogPostTag.tag_id', 'BlogTag.id');
 *
 * // Let's define cross references
 * $schema->setCrossReference('BlogPost', 'BlogPostTag', 'BlogTag');
 * </code>
 *
 * @package Joiner
 */

class Joiner_Schema {

    private $tables = array();
    private $rels = array();


    /**
     * Add a table name in the Schema, optionally defining an alias name
     * $schema->setRelation('sql_guys', 'Guys');
     *
     * @param string $table
     * @param string $name
     * @return Joiner_Schema
     */
    function setTable($table, $name = NULL) {
        if (!$name) {
            $name = $table;
        }

        $this->tables[$name] = $table;
        return $this;

    }

    /**
     * Set a relation between two previously defined tables in the Schema
     * $schema->setRelation('TableOneAlias.key', 'TableTwoAlias.key');
     *
     * @param string $relOne
     * @param string $relTwo
     * @return Joiner_Schema
     */

    function setRelation($relOne, $relTwo) {

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

    /**
     * Set a relation between @param $nameOne and @param $nameTwo using
     * @param $between as cross reference table.
     *
     * The relation between @param $between and @param $nameOne and the
     * relation between @param $between and @param $nameTwo must be previously
     * defined.
     *
     * @param string $nameOne Table alias
     * @param string $between Table alias
     * @param string $nameTwo Table alias
     * @return Joiner_Schema
     */

    function setCrossReference($nameOne, $between, $nameTwo) {

        $this->rels[$nameOne][$nameTwo]['ref'] = $between;
        $this->rels[$nameTwo][$nameOne]['ref'] = $between;

        return $this;

    }

    /**
     * Return the database table name of a given table alias name.
     *
     * @param string $name The alias of the table in this schema
     * @return string database table name
     */
    function getTableByName($name) {
        return isset($this->tables[$name])? $this->tables[$name]: $name;
    }

    /**
     * Used internally for magic joins
     *
     * @param string $nameOne Table alias name
     * @param string $nameTwo Table alias name
     * @return array An array describing the relations
     */
    function getRelation($nameOne, $nameTwo) {

        if (isset($this->rels[$nameOne][$nameTwo])) {
            return $this->rels[$nameOne][$nameTwo];

        }

        throw new Exception("No relations between <i>$nameOne</i> and <i>$nameTwo</i> in this schema");

    }

    /**
     * Used internally to obtain the table expression.
     *
     * The example below works thanks to this method
     *
     * <code>
     * Joiner::getAdapter()->getSchema()->setTable('blog_posts', 'Post');
     *
     * // Will produce SELECT p.* FROM blog_posts AS p
     * Joiner::getAdapter()->getTable('Post p');
     *
     * // Will produce SELECT Post.* FROM blog_posts AS Post
     * Joiner::getAdapter()->getTable('Post');
     * </code>
     *
     * @param string $tableExpr (Examples: table_name asName, internal_name asName, table_name, internal_name)
     * @return array array('table' => 'db table name', 'name' => 'table alias in this schema', 'as' => 'alias to be used in the sql query')
     */
    function resolveTableExpr($tableExpr) {
        if (preg_match('#^(?:([a-z0-9_]+)\s+)?([a-z0-9_]+)$#i', $tableExpr, $parts)) {

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
