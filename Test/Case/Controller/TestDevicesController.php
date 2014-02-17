<?php
App::uses('Controller', 'Controller');

class TestDevicesController extends Controller {

	public $uses = array('TestDevice');

	public $components = array('Sifter.Sifter', 'Paginator');

	public $redirecTest = null;

	public function redirect($url, $status = null, $exit = true) {
		return $this->redirecTest = compact('url', 'status', 'exit');
	}

	public function index() {
		$this->set('devices', $this->Paginator->paginate());
	}

	public function add() {
	}

	public function render($view = null, $layout = null) {
		$response = new CakeResponse();
		$response->body('abc');
		return $response;
	}
}