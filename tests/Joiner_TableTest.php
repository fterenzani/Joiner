<?php

require_once dirname(__FILE__).'/../Joiner.php';
require_once dirname(__FILE__).'/../Table.php';


/**
 * Test class for Joiner_Table.
 * Generated by PHPUnit on 2011-03-11 at 17:18:17.
 */
class Joiner_TableTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Joiner_Table
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $db = $this->adapter = Joiner::setAdapter('test', 'sqlite::memory:');

        $db->exec("create table a (c1, c2, c3)");
        $db->exec("create table b (c1, c2, c3)");
        $db->exec("create table c (c1, c2, c3)");
        $db->exec("create table z (a_c1, c_c1)");
        $db->exec("insert into a values ('1', '2', '3')");
        $db->exec("insert into a values ('2', '4', '5')");
        $db->exec("insert into b values ('1', 'b', 'b')");
        $db->exec("insert into b values ('2', '4', '5')");
        $db->exec("insert into c values ('1', '6', '7')");
        $db->exec("insert into c values ('2', '8', '9')");
        $db->exec("insert into z values ('1', '2')");

        $db->getSchema()->setTable('a')->setTable('b')
                ->setRelation('a.c1', 'b.c1')
                ->setTable('z')
                ->setRelation('z.a_c1', 'a.c1')
                ->setRelation('z.c_c1', 'c.c1')
                ->setCrossReference('a', 'z', 'c');

        $this->object = $this->adapter->getTable('a');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    public function testGetAdapter() {
        $this->assertEquals(true, $this->object->getAdapter() instanceof Joiner_Sqlite_Adapter);

    }

    public function testJoin() {

        $table = $this->adapter->getTable('a')->join('b');

        $this->assertEquals(true, $table instanceof Joiner_Sqlite_Table);
        $this->assertEquals(false, $table === $this->object);
        $this->assertEquals(true, $table[0]->c1 === '1');

        $this->assertAttributeEquals(' INNER JOIN b AS b ON a.c1 = b.c1 ',
                'join', $table);

        // get a crean table
        $table = $table->getAdapter()->getTable('a')->join('c');

        $this->assertAttributeEquals(' INNER JOIN z AS z ON a.c1 = z.a_c1  INNER JOIN  c AS c ON z.c_c1 = c.c1 ',
                'join', $table);

    }

    public function testLeftJoin() {

        $table = $this->adapter->getTable('a')->leftJoin('b');

        $this->assertAttributeEquals(' LEFT JOIN b AS b ON a.c1 = b.c1 ',
                'join', $table);

        // get a crean table
        $table = $table->getAdapter()->getTable('a')->leftJoin('c');

        $this->assertAttributeEquals(' LEFT JOIN z AS z ON a.c1 = z.a_c1  LEFT JOIN  c AS c ON z.c_c1 = c.c1 ',
                'join', $table);

    }

    /**
     * @todo Implement testWhere().
     */
    public function testWhere() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOrWhere().
     */
    public function testOrWhere() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAndWhere().
     */
    public function testAndWhere() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testWhereIn().
     */
    public function testWhereIn() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAndWhereIn().
     */
    public function testAndWhereIn() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOrWhereIn().
     */
    public function testOrWhereIn() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testBind().
     */
    public function testBind() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGroupBy().
     */
    public function testGroupBy() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddGroupBy().
     */
    public function testAddGroupBy() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOrderBy().
     */
    public function testOrderBy() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddOrderBy().
     */
    public function testAddOrderBy() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testLimit().
     */
    public function testLimit() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOffset().
     */
    public function testOffset() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testHaving().
     */
    public function testHaving() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAndHaving().
     */
    public function testAndHaving() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOrHaving().
     */
    public function testOrHaving() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSelect().
     */
    public function testSelect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddSelect().
     */
    public function testAddSelect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDistinct().
     */
    public function testDistinct() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testSet().
     */
    public function testSet() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testMergeSet().
     */
    public function testMergeSet() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testToSqlSelect().
     */
    public function testToSqlSelect() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testToSqlSelectCount().
     */
    public function testToSqlSelectCount() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testToSqlUpdate().
     */
    public function testToSqlUpdate() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement test_parseSet().
     */
    public function test_parseSet() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testToSqlDelete().
     */
    public function testToSqlDelete() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testToSqlInsert().
     */
    public function testToSqlInsert() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testToSqlReplace().
     */
    public function testToSqlReplace() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement test_parseValues().
     */
    public function test_parseValues() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testTruncate().
     */
    public function testTruncate() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCopy().
     */
    public function testCopy() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testApply().
     */
    public function testApply() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetMetas().
     */
    public function testGetMetas() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testRewind().
     */
    public function testRewind() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCurrent().
     */
    public function testCurrent() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testKey().
     */
    public function testKey() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testNext().
     */
    public function testNext() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testValid().
     */
    public function testValid() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOffsetExists().
     */
    public function testOffsetExists() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOffsetGet().
     */
    public function testOffsetGet() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOffsetSet().
     */
    public function testOffsetSet() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testOffsetUnset().
     */
    public function testOffsetUnset() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCount().
     */
    public function testCount() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testLoad().
     */
    public function testLoad() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testUnload().
     */
    public function testUnload() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFetch().
     */
    public function testFetch() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFetchAll().
     */
    public function testFetchAll() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFetchOne().
     */
    public function testFetchOne() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFetchColumn().
     */
    public function testFetchColumn() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFetchColumns().
     */
    public function testFetchColumns() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testFetchCount().
     */
    public function testFetchCount() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testUseModel().
     */
    public function testUseModel() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetModelClass().
     */
    public function testGetModelClass() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testInsert().
     */
    public function testInsert() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testReplace().
     */
    public function testReplace() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testUpdate().
     */
    public function testUpdate() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDelete().
     */
    public function testDelete() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
}
?>
