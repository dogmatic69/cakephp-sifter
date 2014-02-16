<?php
App::uses('Model', 'Model');
App::uses('TestDevice', 'Sifter.Test/TestApp/Model');
App::uses('TestDeviceType', 'Sifter.Test/TestApp/Model');

class TestMedia extends Model {
	public $useTable = 'media';

	public $useDbConfig = 'test';

	public $belongsTo = array(
		'TestDevice',
	);

	public $sifter = array(
		'TestDeviceType.name',
		'Media.device_id',
		'ext' => array(

		),
		'active',
		'latitude',
	);
	public $actsAs = array(
		'Sifter.Sifter'
	);

	protected function _findCustom($state, $query, $results = array()) {
		if ($state == 'before') {

			return $query;
		}

		return $results;
	}

	protected function _findCustomMerge($state, $query, $results = array()) {
		if ($state == 'before') {

			$query['conditions'] = Hash::merge($query['conditions'], array(
				'TestMedia.device_id' => 'device-1',
			));

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