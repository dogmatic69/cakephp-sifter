<?php
App::uses('Model', 'Model');
App::uses('TestMedia', 'Sifter.Test/TestApp/Model');
App::uses('TestDevice', 'Sifter.Test/TestApp/Model');

class TestDeviceType extends Model {

	public $useTable = 'device_types';

	public $useDbConfig = 'test';

	public $hasMany = array(
		'TestDeviceType',
	);
}