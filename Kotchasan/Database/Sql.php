<?php
/*
 * @filesource Kotchasan/Database/Sql.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan\Database;

use \Kotchasan\Database\QueryBuilder;

/**
 * SQL Function
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Sql
{
  /**
   * คำสั่ง SQL ที่เก็บไว้
   *
   * @var string
   */
  protected $sql;
  /**
   * ตัวแปรเก็บพารามิเตอร์สำหรับการ bind
   *
   * @var array
   */
  protected $values;

  /**
   * class constructer
   *
   * @param string $sql
   */
  public function __construct($sql = null)
  {
    $this->sql = $sql;
    $this->values = array();
  }

  /**
   * คืนค่าคำสั่ง SQL เป็น string
   *
   * @return string
   */
  public function text()
  {
    return $this->sql;
  }

  /**
   * คืนค่าแอเร์ยเก็บพารามิเตอร์สำหรับการ bind รวมกับ $values
   *
   * @param array $values
   * @return array
   */
  public function getValues($values = array())
  {
    if (empty($values)) {
      return $this->values;
    }
    foreach ($this->values as $key => $value) {
      $values[$key] = $value;
    }
    return $values;
  }

  /**
   * สร้าง Object Sql
   *
   * @param string $sql
   */
  public static function create($sql)
  {
    return new static($sql);
  }

  /**
   * ใส่ `` ครอบชื่อคอลัมน์
   * ชื่อคอลัมน์ต้องเป็น ภาษาอังกฤษ ตัวเลข และ _ เท่านั้น
   * ถ้ามีอักขระอื่นนอกจากนี้ คืนค่า ข้อความที่ส่งมา
   *
   * @param string $column_name
   * @return string
   * @throws \InvalidArgumentException ถ้ารูปแบบของ $column_name ไม่ถูกต้อง
   *
   * @assert ('U.id') [==] 'U.`id`'
   * @assert ('U1.id') [==] 'U1.`id`'
   * @assert (field_name) [==] '`field_name`'
   * @assert ('table_name.field_name') [==] '`table_name`.`field_name`'
   * @assert ('`table_name`.`field_name`') [==] '`table_name`.`field_name`'
   * @assert ('DATE(day)') [throws] InvalidArgumentException
   */
  public static function fieldName($column_name)
  {
    if ($column_name instanceof self) {
      return $column_name->text();
    } elseif (preg_match('/^([A-Z0-9]{1,2}\.)?`?([a-zA-Z0-9_]+)`?$/', $column_name, $match)) {
      // U.id, U1.id, field_name
      return "$match[1]`$match[2]`";
    } elseif (preg_match('/^`?([a-zA-Z0-9_]+)`?\.`?([a-zA-Z0-9_]+)`?$/', $column_name, $match)) {
      // table_name.field_name
      return "`$match[1]`.`$match[2]`";
    }
    throw new \InvalidArgumentException('Invalid arguments in fieldName');
  }

  /**
   * สร้างคำสั่ง WHERE
   *
   * @param mixed $condition
   * @param string $operator (optional) เช่น AND หรือ OR
   * @param string $id (optional )ชื่อฟิลด์ที่เป็น key
   * @return \static
   *
   * @assert WHERE(1)->text() [==] "`id` = 1"
   * @assert WHERE('1')->text() [==] "`id` = '1'"
   * @assert WHERE(0.1)->text() [==] "`id` = 0.1"
   * @assert WHERE('ทดสอบ')->text() [==] "`id` = 'ทดสอบ'"
   * @assert WHERE(null)->text() [==] "`id` = NULL"
   * @assert WHERE('SELECT * FROM')->text() [==] "`id` = :id0"
   * @assert WHERE(Sql::create('EXISTS SELECT FROM WHERE'))->text() [==] "EXISTS SELECT FROM WHERE"
   * @assert WHERE(array('id', '=', 1))->text() [==] "`id` = 1"
   * @assert WHERE(array('U.id', '2017-01-01 00:00:00'))->text() [==] "U.`id` = '2017-01-01 00:00:00'"
   * @assert WHERE(array('id', 'IN', array(1, '2', null)))->text() [==] "`id` IN (1, '2', NULL)"
   * @assert WHERE(array('id', 'SELECT * FROM'))->text() [==] "`id` = :id0"
   * @assert WHERE(array('U.`id`', 'NOT IN', Sql::create('SELECT * FROM')))->text() [==] "U.`id` NOT IN SELECT * FROM"
   * @assert WHERE(array(array('id', 'IN', array(1, '2', null))))->text() [==] "`id` IN (1, '2', NULL)"
   * @assert WHERE(array(array('U.id', 1), array('U.id', '!=', '1')))->text() [==] "U.`id` = 1 AND U.`id` != '1'"
   * @assert WHERE(array(array(Sql::MONTH('create_date'), 1), array(Sql::YEAR('create_date'), 1)))->text() [==] "MONTH(`create_date`) = 1 AND YEAR(`create_date`) = 1"
   * @assert WHERE(array(array('id', array(1, 'a')), array('id', array('G.id', 'G.`id2`'))))->text() [==] "`id` IN (1, 'a') AND `id` IN ('G.id', G.`id2`)"
   * @assert WHERE(array(Sql::YEAR('create_date'), Sql::YEAR('`create_date`')))->text() [==] "YEAR(`create_date`) = YEAR(`create_date`)"
   * @assert WHERE(array('ip', 'NOT IN', array('', '192.168.1.2')))->text() [==] "`ip` NOT IN ('', '192.168.1.2')"
   */
  public static function WHERE($condition, $operator = 'AND', $id = 'id')
  {
    $obj = new static;
    $obj->sql = $obj->buildWhere($condition, $obj->values, $operator, $id);
    return $obj;
  }

  /**
   * create SQL WHERE command
   *
   * @param mixed $condition
   * @param array $values แอเรย์สำหรับรับค่า value สำหรับการ bind
   * @param string $operator เช่น AND หรือ OR
   * @param string $id ชื่อฟิลด์ที่เป็น key
   * @return string
   */
  private function buildWhere($condition, &$values, $operator, $id)
  {
    if (is_array($condition)) {
      if (is_array($condition[0])) {
        $qs = array();
        foreach ($condition as $items) {
          $qs[] = $this->buildWhere($items, $values, $operator, $id);
        }
        $sql = implode(' '.$operator.' ', $qs);
      } else {
        if (sizeof($condition) == 2) {
          $operator = '=';
          $value = $condition[1];
        } else {
          $operator = trim($condition[1]);
          $value = $condition[2];
        }
        $key = self::fieldName($condition[0]);
        if (is_array($value) && $operator == '=') {
          $operator = 'IN';
        }
        $sql = $key.' '.$operator.' '.self::quoteValue($key, $value, $values);
      }
    } elseif ($condition instanceof self || $condition instanceof QueryBuilder) {
      // Sql หรือ QueryBuilder ไม่มี column_name
      $sql = $condition->text();
      $values = $condition->getValues($values);
    } else {
      // ใช้ $id เป็น column_name
      $sql = self::fieldName($id).' = '.self::quoteValue($id, $condition, $values);
    }
    return $sql;
  }

  /**
   * แปลงค่า Value สำหรับใช้ใน query
   *
   * @param mixed $value
   * @param array $values แอเรย์สำหรับรับค่า value สำหรับการ bind
   * @return string
   * @throws \InvalidArgumentException ถ้ารูปแบบของ $value ไม่ถูกต้อง
   *
   * @assert ('id', 'ทดสอบ') [==] "'ทดสอบ'"
   * @assert ('id', 'test') [==] "'test'"
   * @assert ('id', 'abcde012345') [==] "'abcde012345'"
   * @assert ('id', 123456) [==] 123456
   * @assert ('id', 0.1) [==] 0.1
   * @assert ('id', null) [==] 'NULL'
   * @assert ('id', 'U.id') [==] "'U.id'"
   * @assert ('id', 'U.`id`') [==] 'U.`id`'
   * @assert ('id', 'table_name.id') [==] "'table_name.id'"
   * @assert ('id', '`table_name`.`id`') [==] '`table_name`.`id`'
   * @assert ('id', 'INSERT INTO') [==] ':id0'
   * @assert ('id', array(1, '2', null)) [==] "(1, '2', NULL)"
   * @assert ('id', '0x64656') [==] ':id0'
   * @assert ('`table_name`.`id`', '0x64656') [==] ':tablenameid0'
   * @assert ('U1.`id`', '0x64656') [==] ':u1id0'
   * @assert ('U.id', '0x64656') [==] ':uid0'
   */
  public static function quoteValue($column_name, $value, &$values)
  {
    if (is_array($value)) {
      $qs = array();
      foreach ($value as $v) {
        $qs[] = self::quoteValue($column_name, $v, $values);
      }
      $sql = '('.implode(', ', $qs).')';
    } elseif ($value === NULL) {
      $sql = 'NULL';
    } elseif ($value === '') {
      $sql = "''";
    } elseif (is_string($value)) {
      if (preg_match('/^([0-9\s\r\n\t\.\_\-:]+)$/', $value)) {
        // ตัวเลข จำนวนเงิน เบอร์โทร วันที่
        $sql = "'$value'";
      } elseif (preg_match('/^(`?[a-zA-Z0-9_\-]+`?\.`?)?`[a-zA-Z0-9_\-]+`$/', $value) && !preg_match('/0x[0-9]+/is', $value)) {
        // ชื่อฟิลด์ต้องอยู่ภายใต้ `` เช่น U.`id` `table_name`.`id`
        $sql = $value;
      } elseif (!preg_match('/[\s\r\n\t`;\(\)\*\=<>\/\'"]+/s', $value) && !preg_match('/(UNION|INSERT|DELETE|DROP|0x[0-9]+)/is', $value)) {
        // ข้อความที่ไม่มีช่องว่างหรือรหัสที่อาจเป็น SQL
        $sql = "'$value'";
      } else {
        $sql = ':'.strtolower(preg_replace('/[`\.\s\-_]+/', '', $column_name)).sizeof($values);
        $values[$sql] = $value;
      }
    } elseif (is_numeric($value)) {
      // ตัวเลข
      $sql = $value;
    } elseif ($value instanceof self) {
      // Sql
      $sql = $value->text();
      $values = $value->getValues($values);
    } elseif ($value instanceof QueryBuilder) {
      // QueryBuilder
      $sql = '('.$value->text().')';
      $values = $value->getValues($values);
    } else {
      throw new \InvalidArgumentException('Invalid arguments in quoteValue');
    }
    return $sql;
  }

  /**
   * ฟังก์ชั่นสร้าง SQL สำหรับหาค่าสูงสุด + 1
   * ใช้ในการหาค่า id ถัดไป
   *
   *
   * @param string $field ชื่อฟิลด์ที่ต้องการหาค่าสูงสุด
   * @param string $table_name ชื่อตาราง
   * @param mixed $condition (optional) query WHERE
   * @param array $values (optional) แอเรย์สำหรับรับค่า value สำหรับการ bind
   * @param string $alias (optional) ชื่อฟิลด์ที่ใช้คืนค่า ไม่ระบุ (null) หมายถึงไม่ต้องการชื่อฟิลด์
   * @param string $operator (optional) เช่น AND หรือ OR
   * @param string $id (optional )ชื่อฟิลด์ที่เป็น key
   * @return \static
   *
   * @assert ('id', '`world`')->text() [==] '(1 + IFNULL((SELECT MAX(`id`) FROM `world` AS X), 0))'
   * @assert ('id', '`world`', array(array('module_id', 'D.`id`')), 'next_id')->text() [==] '(1 + IFNULL((SELECT MAX(`id`) FROM `world` AS X WHERE `module_id` = D.`id`), 0)) AS `next_id`'
   * @assert ('id', '`world`', array(array('module_id', 'D.`id`')), null)->text() [==] '(1 + IFNULL((SELECT MAX(`id`) FROM `world` AS X WHERE `module_id` = D.`id`), 0))'
   */
  public static function NEXT($field, $table_name, $condition = null, $alias = null, $operator = 'AND', $id = 'id')
  {
    $obj = new static;
    if (empty($condition)) {
      $condition = '';
    } else {
      $condition = ' WHERE '.$obj->buildWhere($condition, $obj->values, $operator, $id);
    }
    $obj->sql = '(1 + IFNULL((SELECT MAX(`'.$field.'`) FROM '.$table_name.' AS X'.$condition.'), 0))';
    if (isset($alias)) {
      $obj->sql .= " AS `$alias`";
    }
    return $obj;
  }

  /**
   * ผลรวมของคอลัมน์ที่เลือก
   *
   * @param string $column_name
   * @param string|null $alias
   * @param boolean $distinct false (default) รวมทุกคอลัมน์, true รวมเฉพาะคอลัมน์ที่ไม่ซ้ำ
   * @return \static
   *
   * @assert ('id')->text() [==] 'SUM(`id`)'
   * @assert ('table_name.id', 'id')->text() [==] 'SUM(`table_name`.`id`) AS `id`'
   * @assert ('U.id', 'id', true)->text() [==] 'SUM(DISTINCT U.`id`) AS `id`'
   * @assert ('U1.id', 'id', true)->text() [==] 'SUM(DISTINCT U1.`id`) AS `id`'
   */
  public static function SUM($column_name, $alias = '', $distinct = false)
  {
    return self::create('SUM('.($distinct ? 'DISTINCT ' : '').self::fieldName($column_name).')'.($alias ? " AS `$alias`" : ''));
  }

  /**
   * นับจำนวนเร็คคอร์ดของคอลัมน์ที่เลือก
   *
   * @param string $column_name
   * @param string|null $alias
   * @param boolean $distinct false (default) นับทุกคอลัมน์, true นับเฉพาะคอลัมน์ที่ไม่ซ้ำ
   * @return \static
   *
   * @assert ('id')->text() [==] 'COUNT(`id`)'
   */
  public static function COUNT($column_name = '*', $alias = null, $distinct = false)
  {
    $column_name = $column_name == '*' ? '*' : self::fieldName($column_name);
    return self::create('COUNT('.($distinct ? 'DISTINCT ' : '').$column_name.')'.($alias ? " AS `$alias`" : ''));
  }

  /**
   * หาค่าเฉลี่ยของคอลัมน์ที่เลือก
   *
   * @param string $column_name ชื่อคอลัมน์
   * @param string|null $alias ชื่อรองที่ต้องการ ถ้าไม่ระบุไม่มีชื่อรอง
   * @param boolean $distinct false (default) นับทุกคอลัมน์, true นับเฉพาะคอลัมน์ที่ไม่ซ้ำ
   * @return \static
   *
   * @assert ('id')->text() [==] 'AVG(`id`)'
   */
  public static function AVG($column_name, $alias = null, $distinct = false)
  {
    return self::create('AVG('.($distinct ? 'DISTINCT ' : '').self::fieldName($column_name).')'.($alias ? " AS `$alias`" : ''));
  }

  /**
   * หาค่าต่ำสุด
   *
   * @param string $column_name
   * @param string|null $alias ชื่อรองที่ต้องการ ถ้าไม่ระบุไม่มีชื่อรอง
   * @return \static
   *
   * @assert ('id')->text() [==] 'MIN(`id`)'
   */
  public static function MIN($column_name, $alias = null)
  {
    return self::create('MIN('.self::fieldName($column_name).')'.($alias ? " AS `$alias`" : ''));
  }

  /**
   * หาค่าต่ำสุด
   *
   * @param string $column_name
   * @param string|null $alias ชื่อรองที่ต้องการ ถ้าไม่ระบุไม่มีชื่อรอง
   * @return \static
   *
   * @assert ('id')->text() [==] 'MAX(`id`)'
   */
  public static function MAX($column_name, $alias = null)
  {
    return self::create('MAX('.self::fieldName($column_name).')'.($alias ? " AS `$alias`" : ''));
  }

  /**
   * หาค่าวันที่
   *
   * @param string $column_name
   * @param string|null $alias ชื่อรองที่ต้องการ ถ้าไม่ระบุไม่มีชื่อรอง
   * @return \static
   *
   * @assert ('date')->text() [==] 'DAY(`date`)'
   */
  public static function DAY($column_name, $alias = null)
  {
    return self::create('DAY('.self::fieldName($column_name).')'.($alias ? " AS `$alias`" : ''));
  }

  /**
   * หาค่าเดือน
   *
   * @param string $column_name
   * @param string|null $alias ชื่อรองที่ต้องการ ถ้าไม่ระบุไม่มีชื่อรอง
   * @return \static
   *
   * @assert ('date')->text() [==] 'MONTH(`date`)'
   */
  public static function MONTH($column_name, $alias = null)
  {
    return self::create('MONTH('.self::fieldName($column_name).')'.($alias ? " AS `$alias`" : ''));
  }

  /**
   * หาค่าปี
   *
   * @param string $column_name
   * @param string|null $alias ชื่อรองที่ต้องการ ถ้าไม่ระบุไม่มีชื่อรอง
   * @return \static
   *
   * @assert ('date')->text() [==] 'YEAR(`date`)'
   */
  public static function YEAR($column_name, $alias = null)
  {
    return self::create('YEAR('.self::fieldName($column_name).')'.($alias ? " AS `$alias`" : ''));
  }

  /**
   * สุ่มตัวเลข
   *
   * @param string $column_name
   * @param string|null $alias ชื่อรองที่ต้องการ ถ้าไม่ระบุไม่มีชื่อรอง
   * @return \static
   *
   * @assert ()->text() [==] 'RAND()'
   * @assert ('id')->text() [==] 'RAND() AS `id`'
   */
  public static function RAND($alias = null)
  {
    return self::create('RAND()'.($alias ? " AS `$alias`" : ''));
  }

  /**
   * สร้างคำสั่ง CONCAT หรือ CONCAT_WS
   *
   * @param array $fields รายชื่อฟิลด์
   * @param string|null $alias ชื่อรองที่ต้องการ ถ้าไม่ระบุไม่มีชื่อรอง
   * @param string|null $separator null (defailt) คืนค่าคำสั่ง CONCAT, ถ้าระบุเป็นค่าอื่นคืนค่าคำสั่ง CONCAT_WS
   * @return \static
   *
   * @assert (array('fname', 'lname'))->text() [==] "CONCAT(`fname`, `lname`)"
   * @assert (array('U.fname', 'U.`lname`'), 'displayname')->text() [==] "CONCAT(U.`fname`, U.`lname`) AS `displayname`"
   * @assert (array('fname', 'lname'), 'displayname', ' ')->text() [==] "CONCAT_WS(' ', `fname`, `lname`) AS `displayname`"
   */
  public static function CONCAT($fields, $alias = null, $separator = null)
  {
    $fs = array();
    foreach ($fields as $item) {
      $fs[] = self::fieldName($item);
    }
    return self::create(($separator === null ? 'CONCAT(' : "CONCAT_WS('$separator', ").implode(', ', $fs).($alias ? ") AS `$alias`" : ')'));
  }

  /**
   * สร้างคำสั่ง GROUP_CONCAT
   * @param string $column_name
   * @param string $separator ข้อความเชื่อมฟิลด์เข้าด้วยกัน ค่าเริมต้นคือ ,
   * @param string|null $alias ชื่อรองที่ต้องการ ถ้าไม่ระบุไม่มีชื่อรอง
   * @param boolean $distinct false (default) คืนค่ารายการที่ไม่ซ้ำ
   * @return \self
   *
   * @assert ('C.topic', ', ', 'topic')->text() [==] "GROUP_CONCAT(C.`topic` SEPARATOR ', ') AS `topic`"
   */
  public static function GROUP_CONCAT($column_name, $separator = ',', $alias = null, $distinct = false, $order = null)
  {
    return self::create('GROUP_CONCAT('.($distinct ? 'DISTINCT ' : '').self::fieldName($column_name)." SEPARATOR '$separator')".($alias ? " AS `$alias`" : ''));
  }
}