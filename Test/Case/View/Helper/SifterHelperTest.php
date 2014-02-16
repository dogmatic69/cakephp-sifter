<?php
App::uses('View', 'View');
App::uses('Helper', 'View');
App::uses('SifterHelper', 'Sifter.View/Helper');

App::uses('TestMediaController', 'Sifter.Test/TestApp/Controller');
App::uses('TestDevicesController', 'Sifter.Test/TestApp/Controller');

class_exists('TestMedia');
class_exists('TestDevice');
class_exists('TestDeviceType');

/**
 * SifterHelper Test Case
 *
 */
class SifterHelperTest extends CakeTestCase {

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

		$Request = $this->getMock('CakeRequest', array('_readInput'), array('/test_devices/index'));
		$Response = $this->getMock('CakeResponse', array('send'));
		$Dispatch = new ControllerTestDispatcher();
		$Dispatch->loadRoutes = true;
		$Dispatch->response = $Response;
		$Dispatch->parseParams(new CakeEvent('ControllerTestCase', $Dispatch, array('request' => $Request)));
		$Dispatch->dispatch($Request, $Dispatch->response, array('return' => true));
		$Controller = new TestDevicesController($Request);
		$View = new View($Controller);
		$View ->request['models'] = array(
			'TestDevice' => array('plugin' => null, 'className' => 'TestDevice')
		);
		$this->Sifter = new SifterHelper($View);
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
 * testGetModel method
 *
 * @return void
 */
	public function testGetModel() {
	}

/**
 * testGetFields method
 *
 * @return void
 */
	public function testGetFields() {
	}

}
