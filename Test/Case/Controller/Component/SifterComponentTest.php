<?php
App::uses('ComponentCollection', 'Controller');
App::uses('Controller', 'Controller');
App::uses('Component', 'Controller');
App::uses('SifterComponent', 'Sifter.Controller/Component');
App::uses('TestMedia', 'Sifter.Test/TestApp/Model');
App::uses('TestMediaController', 'Sifter.Test/TestApp/Controller');
App::uses('TestDevicesController', 'Sifter.Test/TestApp/Controller');

class_exists('TestMedia');
class_exists('TestDevice');
class_exists('TestDeviceType');

Router::parseExtensions('json');

/**
 * SifterComponent Test Case
 *
 */
class SifterComponentTest extends ControllerTestCase {

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

	public function testBeforeRender() {
		// $result = $this->testAction('/test_devices/index');
		// $this->assertTrue(in_array('Sifter.Sifter', $this->controller->helpers));
	}

/**
 * test redirects for PRG when data is submitted that is valid
 *
 * @dataProvider redirectDataProvider
 * @return void
 */
	public function testRedirect($data, $expected) {
		$result = $this->testAction($data['url'], $data['pass']);
		$this->assertEquals($expected, $this->controller->redirecTest);
	}

	public function redirectDataProvider() {
		return array(
			'simple 1' => array(
				array(
					'pass' => array(
						'data' => array(
							'Sifter' => array(
								'search_field' => null,
								'search_value' => null,
							),
							'TestDevice' => array(
								'name' => 'device-1',
							),
						),
						'method' => 'post',
					),
					'url' => '/test_devices/index',
				),
				array(
					'url' => array(
						'TestDevice.name' => 'device-1',
						'return' => true,
						'bare' => true,
						'requested' => true,
					),
					'status' => null,
					'exit' => true,
				)
			),
			'simple 2' => array(
				array(
					'pass' => array(
						'data' => array(
							'Sifter' => array(
								'search_field' => null,
								'search_value' => null,
							),
							'TestDevice' => array(
								'name' => 'device-1',
							),
							'TestDeviceType' => array(
								'name' => 'blaa',
							)
						),
						'method' => 'post',
					),
					'url' => '/test_devices/index',
				),
				array(
					'url' => array(
						'TestDevice.name' => 'device-1',
						'TestDeviceType.name' => 'blaa',
						'return' => true,
						'bare' => true,
						'requested' => true,
					),
					'status' => null,
					'exit' => true,
				)
			),
			'missing sifter data' => array(
				array(
					'pass' => array(
						'data' => array(
							'TestDevice' => array(
								'name' => 'device-1',
							),
						),
						'method' => 'post',
					),
					'url' => '/test_devices/index',
				),
				null
			),
			'no sifter fields' => array(
				array(
					'pass' => array(
						'data' => array(
							'TestDevice' => array(
								'foo_bar' => 'device-1',
							),
						),
						'method' => 'post',
					),
					'url' => '/test_devices/index',
				),
				null
			),
			'no process' => array(
				array(
					'pass' => array(
						'data' => array(
							'Sifter' => array(
								'search_field' => null,
								'search_value' => null,
							),
							'TestDevice' => array(
								'name' => 'device-1',
							),
						),
						'method' => 'post',
					),
					'url' => '/test_devices/add',
				),
				null,
			),
		);
	}

/**
 * test requests for ajax data / autocomplete
 *
 *	@dataProvider ajaxRequestsDataProvider
 */
	public function testAjaxRequests($data, $expected) {
		$result = $this->testAction($data['url'], $data['pass']);
		$this->assertEquals($expected, $result['sifter']);
	}

	public function ajaxRequestsDataProvider() {
		return array(
			'device 1' => array(
				array(
					'url' => '/test_devices/index/foo.json',
					'pass' => array(
						'data' => array(
							'Sifter' => array(
								'search_field' => 'TestDevice.name',
								'search_value' => '-1',
							),
							'TestDevice' => array(
								'name' => '-1',
							),
						),
						'method' => 'post',
						'return' => 'vars',
					)
				),
				array(
					'error' => false,
					'message' => false,
					'data' => array(
						'device-1' => 'device-1',
					),
				)
			),
			'device 2' => array(
				array(
					'url' => '/test_devices/index/foo.json',
					'pass' => array(
						'data' => array(
							'Sifter' => array(
								'search_field' => 'TestDevice.name',
								'search_value' => '-2',
							),
							'TestDevice' => array(
								'name' => '-2',
							),
						),
						'method' => 'post',
						'return' => 'vars',
					)
				),
				array(
					'error' => false,
					'message' => false,
					'data' => array(
						'device-2' => 'device-2',
					),
				)
			),
			'no results' => array(
				array(
					'url' => '/test_devices/index/foo.json',
					'pass' => array(
						'data' => array(
							'Sifter' => array(
								'search_field' => 'TestDevice.name',
								'search_value' => 'foo bar',
							),
							'TestDevice' => array(
								'name' => 'foo bar',
							),
						),
						'method' => 'post',
						'return' => 'vars',
					)
				),
				array(
					'error' => true,
					'message' => 'No data found for the selected term',
					'data' => null,
				)
			),
			'multi' => array(
				array(
					'url' => '/test_devices/index/foo.json',
					'pass' => array(
						'data' => array(
							'Sifter' => array(
								'search_field' => 'TestDevice.name',
								'search_value' => '-',
							),
							'TestDevice' => array(
								'name' => '-',
							),
						),
						'method' => 'post',
						'return' => 'vars',
					)
				),
				array(
					'error' => false,
					'message' => false,
					'data' => array(
						'device-1' => 'device-1',
						'device-2' => 'device-2',
					),
				)
			),
			// 'not valid field' => array(
			// 	array(
			//		'url' => '/test_devices/index/foo.json',
			// 		'pass' => array(
			// 			'data' => array(
			// 				'Sifter' => array(
			// 					'search_field' => 'TestDevice.id',
			// 					'search_value' => 'foo bar',
			// 				),
			// 				'TestDevice' => array(
			// 					'name' => 'foo bar',
			// 				),
			// 			),
			// 			'method' => 'post',
			// 			'return' => 'vars',
			// 		)
			// 	),
			// 	array(
			// 		'error' => true,
			// 		'message' => 'No data found for the selected term',
			// 		'data' => null,
			// 	)
			// ),
		);
	}

}
