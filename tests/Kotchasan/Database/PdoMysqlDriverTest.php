<?php

namespace Kotchasan\Database;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-11-12 at 15:44:50.
 */
class PdoMysqlDriverTest extends \PHPUnit_Framework_TestCase
{
  /**
   * @var PdoMysqlDriver
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    $this->object = new PdoMysqlDriver();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown()
  {

  }

  /**
   * Generated from @assert (array('update' => '`user`', 'where' => '`id` = 1', 'set' => array('`id` = 1', "`email` = 'admin@localhost'"))) [==] "UPDATE `user` SET `id` = 1, `email` = 'admin@localhost' WHERE `id` = 1".
   *
   * @covers Kotchasan\Database\PdoMysqlDriver::makeQuery
   */
  public function testMakeQuery()
  {

    $this->assertEquals(
      "UPDATE `user` SET `id` = 1, `email` = 'admin@localhost' WHERE `id` = 1", $this->object->makeQuery(array('update' => '`user`', 'where' => '`id` = 1', 'set' => array('`id` = 1', "`email` = 'admin@localhost'")))
    );
  }

  /**
   * Generated from @assert (array('insert' => '`user`', 'values' => array('id' => 1, 'email' => 'admin@localhost'))) [==] "INSERT INTO `user` (`id`, `email`) VALUES (:id, :email)".
   *
   * @covers Kotchasan\Database\PdoMysqlDriver::makeQuery
   */
  public function testMakeQuery2()
  {

    $this->assertEquals(
      "INSERT INTO `user` (`id`, `email`) VALUES (:id, :email)", $this->object->makeQuery(array('insert' => '`user`', 'values' => array('id' => 1, 'email' => 'admin@localhost')))
    );
  }

  /**
   * Generated from @assert (array('select'=>'*', 'from'=>'`user`','where'=>'`id` = 1', 'order' => '`id`', 'start' => 1, 'limit' => 10, 'join' => array(" INNER JOIN ..."))) [==] "SELECT * FROM `user` INNER JOIN ... WHERE `id` = 1 ORDER BY `id` LIMIT 1,10".
   *
   * @covers Kotchasan\Database\PdoMysqlDriver::makeQuery
   */
  public function testMakeQuery3()
  {

    $this->assertEquals(
      "SELECT * FROM `user` INNER JOIN ... WHERE `id` = 1 ORDER BY `id` LIMIT 1,10", $this->object->makeQuery(array('select' => '*', 'from' => '`user`', 'where' => '`id` = 1', 'order' => '`id`', 'start' => 1, 'limit' => 10, 'join' => array(" INNER JOIN ...")))
    );
  }

  /**
   * Generated from @assert (array('select'=>'*', 'from'=>'`user`','where'=>'`id` = 1', 'order' => '`id`', 'start' => 1, 'limit' => 10, 'group' => '`id`')) [==] "SELECT * FROM `user` WHERE `id` = 1 GROUP BY `id` ORDER BY `id` LIMIT 1,10".
   *
   * @covers Kotchasan\Database\PdoMysqlDriver::makeQuery
   */
  public function testMakeQuery4()
  {

    $this->assertEquals(
      "SELECT * FROM `user` WHERE `id` = 1 GROUP BY `id` ORDER BY `id` LIMIT 1,10", $this->object->makeQuery(array('select' => '*', 'from' => '`user`', 'where' => '`id` = 1', 'order' => '`id`', 'start' => 1, 'limit' => 10, 'group' => '`id`'))
    );
  }

  /**
   * Generated from @assert (array('delete' => '`user`', 'where' => '`id` = 1')) [==] "DELETE FROM `user` WHERE `id` = 1".
   *
   * @covers Kotchasan\Database\PdoMysqlDriver::makeQuery
   */
  public function testMakeQuery5()
  {

    $this->assertEquals(
      "DELETE FROM `user` WHERE `id` = 1", $this->object->makeQuery(array('delete' => '`user`', 'where' => '`id` = 1'))
    );
  }

  /**
   * Generated from @assert ('id', '`world`', array(array('module_id', 'D.id'))) [==] '(1 + IFNULL((SELECT MAX(`id`) FROM `world` WHERE `module_id` = D.`id`), 0)) AS `id`'.
   *
   * @covers Kotchasan\Database\PdoMysqlDriver::buildNext
   */
  public function testBuildNext()
  {

    $this->assertEquals(
      '(1 + IFNULL((SELECT MAX(`id`) FROM `world` WHERE `module_id` = D.`id`), 0)) AS `id`', $this->object->buildNext('id', '`world`', array(array('module_id', 'D.id')))
    );
  }

  /**
   * @covers Kotchasan\Database\PdoMysqlDriver::connect
   * @todo   Implement testConnect().
   */
  public function testConnect()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Kotchasan\Database\PdoMysqlDriver::fieldCount
   * @todo   Implement testFieldCount().
   */
  public function testFieldCount()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Kotchasan\Database\PdoMysqlDriver::getFields
   * @todo   Implement testGetFields().
   */
  public function testGetFields()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Kotchasan\Database\PdoMysqlDriver::insert
   * @todo   Implement testInsert().
   */
  public function testInsert()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Kotchasan\Database\PdoMysqlDriver::select
   * @todo   Implement testSelect().
   */
  public function testSelect()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Kotchasan\Database\PdoMysqlDriver::selectDB
   * @todo   Implement testSelectDB().
   */
  public function testSelectDB()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Kotchasan\Database\PdoMysqlDriver::update
   * @todo   Implement testUpdate().
   */
  public function testUpdate()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }

  /**
   * @covers Kotchasan\Database\PdoMysqlDriver::close
   * @todo   Implement testClose().
   */
  public function testClose()
  {
    // Remove the following lines when you implement this test.
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }
}