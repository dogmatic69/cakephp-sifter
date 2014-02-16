<?php
App::uses('SifterBehavior', 'Sifter.Model/Behavior');
App::uses('TestMedia', 'Sifter.Test/TestApp/Model');

class_exists('TestMedia');
class_exists('TestDevice');
class_exists('TestDeviceType');

/**
 * SifterBehavior Test Case
 *
 */
class SifterBehaviorTest extends CakeTestCase {

	public $fixtures = array(
		'plugin.sifter.device',
		'plugin.sifter.device_type',
		'plugin.sifter.media',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Sifter = new SifterBehavior();
		$this->Media = ClassRegistry::init('TestMedia');
		$this->Device = $this->Media->TestDevice;
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Sifter);

		parent::tearDown();
	}

/**
 * test setup from behavior / model config
 *
 * @dataProvider deviceConfigDataProvider
 */
	public function testSetup($data, $expected) {
		$result = $this->Device->sifterConfig();
		$this->assertEquals($expected, $result);
	}

/**
 * test sifter config
 *
 * @dataProvider deviceConfigDataProvider
 * @return void
 */
	public function testSifterConfig($data, $expected) {
		$result = $this->Device->sifterConfig('sifter');
		$this->assertEquals($expected['sifter'], $result);

		$result = $this->Device->sifterConfig('fields');
		$this->assertEquals($expected['fields'], $result);
	}

	public function testConfigWithDeepRelation() {
		$expected = array(
			'allowedMethods' => array('admin_index'),
			'requiresPost' => false,
			'plugin' => null,
			'alias' => 'TestMedia',
			'name' => 'TestMedia',
		);
		$result = $this->Media->sifterConfig('sifter');
		$this->assertEquals($expected, $result);

		$expected = array(
			'TestDeviceType.name' => array(
				'input' => array(
					'field' => 'TestDeviceType.name',
					'type' => 'text',
					'label' => 'TestDeviceType Name',
					'placeholder' => 'Search by TestDeviceType Name',
					'required' => false,
				),
				'fieldType' => 'string',
				'operator' => 'LIKE',
				'ownModel' => false,
				'parentModel' => 'TestMedia',
				'viewVariable' => null,
			),
			'Media.device_id' => array(
				'input' => array(
					'field' => 'Media.device_id',
					'type' => 'select',
					'label' => 'Filter by Device',
					'multiple' => true,
					'empty' => false,
					'options' => array(
						'className' => null,
						'method' => 'list',
						'methodType' => 'customFind',
					),
					'label' => 'Media Device',
					'placeholder' => 'Search by Media Device',
					'required' => false,
				),
				'fieldType' => 'string',
				'operator' => 'IN',
				'ownModel' => false,
				'parentModel' => 'TestMedia',
				'viewVariable' => 'devices',
			),
			'TestMedia.ext' => array(
				'input' => array(
					'field' => 'TestMedia.ext',
					'type' => 'text',
					'label' => 'Ext',
					'placeholder' => 'Search by Ext',
					'required' => false,
				),
				'fieldType' => 'string',
				'operator' => 'LIKE',
				'ownModel' => true,
				'parentModel' => false,
				'viewVariable' => null,
			),
			'TestMedia.active' => array(
				'input' => array(
					'field' => 'TestMedia.active',
					'type' => 'select',
					'label' => 'Active',
					'placeholder' => 'Search by Active',
					'required' => false,
					'options' => array(
						1 => 'True / Active',
						0 => 'False / Inactive',
					),
					'empty' => 'All records',
				),
				'fieldType' => 'boolean',
				'operator' => '=',
				'ownModel' => true,
				'parentModel' => false,
				'viewVariable' => 'actives',
			),
			'TestMedia.latitude' => array(
				'input' => array(
					'field' => 'TestMedia.latitude',
					'type' => 'number',
					'label' => 'Latitude',
					'placeholder' => 'Search by Latitude',
					'required' => false,
					'min' => -299,
					'max' => 299,
					'step' => 0.000001,
				),
				'fieldType' => 'float',
				'operator' => 'BETWEEN',
				'ownModel' => true,
				'parentModel' => false,
				'viewVariable' => null,
			)
		);
		$result = $this->Media->sifterConfig('fields');
		$this->assertEquals($expected, $result);
	}

/**
 * device config data provider
 */
	public function deviceConfigDataProvider() {
		return array(
			array(
				null,
				array(
					'sifter' => array(
						'allowedMethods' => array(
							'admin_index', 'foo', 'bar', 'admin_baz', 'index',
						),
						'requiresPost' => false,
						'plugin' => null,
						'alias' => 'TestDevice',
						'name' => 'TestDevice',
					),
					'fields' => array(
						'TestDevice.name' => array(
							'input' => array(
								'field' => 'TestDevice.name',
								'type' => 'text',
								'label' => 'Name',
								'placeholder' => 'Search by Name',
								'required' => false,
							),
							'fieldType' => 'string',
							'operator' => 'LIKE',
							'ownModel' => true,
							'parentModel' => false,
							'viewVariable' => null,
						),
						'TestDevice.created' => array(
							'input' => array(
								'field' => 'TestDevice.created',
								'type' => 'text',
								'class' => 'datetime',
								'data-oldest' => '2014-02-15 23:09:01',
								'data-newest' => '2014-02-15 23:09:01',
								'label' => 'Created',
								'placeholder' => 'Search by Created',
								'required' => false,
							),
							'fieldType' => 'datetime',
							'operator' => 'BETWEEN',
							'ownModel' => true,
							'parentModel' => false,
							'viewVariable' => null,
						),
						'TestDeviceType.name' => array(
							'input' => array(
								'field' => 'TestDeviceType.name',
								'type' => 'text',
								'label' => 'TestDeviceType Name',
								'placeholder' => 'Search by TestDeviceType Name',
								'required' => false,
							),
							'fieldType' => 'string',
							'operator' => 'LIKE',
							'ownModel' => false,
							'parentModel' => 'TestDevice',
							'viewVariable' => null,
						),
					),
				)
			)
		);
	}

}
