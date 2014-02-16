<?php
App::uses('ComponentCollection', 'Controller');
App::uses('Component', 'Controller');
App::uses('SifterComponent', 'Sifter.Controller/Component');

/**
 * SifterComponent Test Case
 *
 */
class SifterComponentTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$Collection = new ComponentCollection();
		$this->Sifter = new SifterComponent($Collection);
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
 * testSift method
 *
 * @return void
 */
	public function testSift() {
	}

}
