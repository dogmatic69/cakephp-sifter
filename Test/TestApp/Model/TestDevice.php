<?php
App::uses('Model', 'Model');
App::uses('TestMedia', 'Sifter.Test/TestApp/Model');
App::uses('TestDeviceType', 'Sifter.Test/TestApp/Model');

class TestDevice extends Model {

	public $useTable = 'devices';

	public $useDbConfig = 'test';

	public $belongsTo = array(
		'TestDeviceType' => array('foreignKey' => 'device_type_id'),
	);

	public $hasMany = array(
		'TestMedia' => array('foreignKey' => 'device_id'),
	);

/**
 * Specify fields to search on
 */
	public $sifter = array(
		'TestDeviceType.name',
	);

/**
 * Configure the behavior
 */
	public $actsAs = array(
		'Sifter.Sifter' => array(
			'fields' => array(
				'TestDevice.name',
				'created',
			),
			'allowedMethods' => array(
				'foo', 'bar', 'admin_baz', 'index',
			),
		),
	);

	protected function _findCustom($state, $query, $results = array()) {
		if ($state == 'before') {

			return $query;
		}

		return $results;
	}

	protected function _findCustomAjax($state, $query, $results = array()) {
		if ($state == 'before') {

			return $query;
		}

		return Hash::combine($results, 'TestDevice.id', 'TestDevice.name');
	}
}