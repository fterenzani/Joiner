<?php

require_once dirname(__FILE__).'/../PDOLogger.php';
require_once dirname(__FILE__).'/../PDOStatementLogger.php';

/**
 * Test class for Joiner_PDOStatementLogger.
 * Generated by PHPUnit on 2011-03-10 at 21:57:17.
 */
class Joiner_PDOStatementLoggerTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var Joiner_PDOStatementLogger
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$pdo = new PDO('sqlite::memory:');
		$this->object = new Joiner_PDOStatementLogger($pdo->query('select 1'));
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
	}

	/**
	 * @todo Implement test__call().
	 */
	public function test__call() {
		Joiner_PDOLogger::$log = array();
		$this->object->fetch();
		$this->assertEquals(1, count(Joiner_PDOLogger::$log));
		$this->assertEquals(3, count(Joiner_PDOLogger::$log[0]));
		$this->assertEquals('PDOStatement::fetch', Joiner_PDOLogger::$log[0][0]);
		$this->assertEquals(array(), Joiner_PDOLogger::$log[0][1]);
		$this->assertEquals(true, is_numeric(Joiner_PDOLogger::$log[0][2]));
	}
}
?>
