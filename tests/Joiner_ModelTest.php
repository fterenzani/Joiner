<?php

require_once dirname(__FILE__).'/../Joiner.php';
require_once dirname(__FILE__).'/../Model.php';

/**
 * Test class for Joiner_Model.
 * Generated by PHPUnit on 2011-03-11 at 14:35:57.
 */
class Joiner_ModelTest extends PHPUnit_Framework_TestCase {
    /**
     * @var Joiner_Model
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $db = Joiner::setAdapter('test', 'sqlite::memory:');
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

        $this->object = $db->findOneBy('a.c1', 1);
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    public function testToArray() {
        $aspected = array (
          'c1' => '1',
          'c2' => '2',
          'c3' => '3',
        );
        $this->assertEquals(true, $this->object->toArray() === $aspected);
    }

    public function testGetRelated() {
        $aspected = array (
          'c1' => '1',
          'c2' => 'b',
          'c3' => 'b',
        );
        $this->assertEquals(true, $this->object->getRelated('b')->fetchOne()->toArray()
                === $aspected);

        $aspected = array (
          'c1' => '2',
          'c2' => '8',
          'c3' => '9',
        );
        $this->assertEquals(true, $this->object->getRelated('c')->fetchOne()->toArray()
                === $aspected);

    }



    public function testOffsetExists() {
        $this->assertEquals(true, isset($this->object['c1']));
    }


    public function testOffsetGet() {
        $this->assertEquals(true, $this->object['c1'] === '1');
    }

    public function testOffsetSet() {
        $this->object['foo'] = 'bar';
        $this->assertEquals(true, $this->object['foo'] === 'bar');
    }

    public function testOffsetUnset() {
        unset($this->object['c1']);
        $this->assertEquals(false, isset($this->object['c1']));
    }
}
?>
