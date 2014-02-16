<?php
App::uses('View', 'View');
App::uses('Helper', 'View');
App::uses('SifterHelper', 'Sifter.View/Helper');

/**
 * SifterHelper Test Case
 *
 */
class SifterHelperTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$View = new View();
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
