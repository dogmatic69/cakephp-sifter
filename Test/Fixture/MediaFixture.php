<?php
/**
 * MediaFixture
 *
 */
class MediaFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'device_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'description' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'ext' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 5, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'date_captured' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'latitude' => array('type' => 'float', 'null' => true, 'default' => null, 'length' => '9,6'),
		'longitude' => array('type' => 'float', 'null' => true, 'default' => null, 'length' => '9,6'),
		'active' => array('type' => 'boolean', 'null' => true, 'default' => null),
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
			'id' => 'media-1',
			'device_id' => 'device-1',
			'description' => 'media 1 abc',
			'ext' => 'jpg',
			'date_captured' => '2014-01-01 00:00:01',
			'latitude' => 1,
			'longitude' => 1,
			'active' => 1,
			'created' => '2014-02-15 23:04:59',
			'modified' => '2014-02-15 23:04:59'
		),
		array(
			'id' => 'media-2',
			'device_id' => 'device-1',
			'description' => 'zxc media 2',
			'ext' => 'jpg',
			'date_captured' => '2014-01-02 00:00:01',
			'latitude' => 1,
			'longitude' => 1,
			'active' => 1,
			'created' => '2014-02-15 23:04:59',
			'modified' => '2014-02-15 23:04:59'
		),
		array(
			'id' => 'media-3',
			'device_id' => 'device-2',
			'description' => 'media qwe 3',
			'ext' => 'jpg',
			'date_captured' => '2014-01-03 00:00:01',
			'latitude' => 1,
			'longitude' => 1,
			'active' => 0,
			'created' => '2014-02-15 23:04:59',
			'modified' => '2014-02-15 23:04:59'
		),
	);

}
