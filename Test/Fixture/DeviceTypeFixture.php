<?php
/**
 * DeviceTypeFixture
 *
 */
class DeviceTypeFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'device_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 5),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 'device-type-1',
			'name' => 'device-type-1',
			'device_count' => 1,
			'created' => '2014-02-15 23:10:28',
			'modified' => '2014-02-15 23:10:28'
		),
		array(
			'id' => 'device-type-2',
			'name' => 'device-type-2',
			'device_count' => 1,
			'created' => '2014-02-15 23:10:28',
			'modified' => '2014-02-15 23:10:28'
		),
	);

}
