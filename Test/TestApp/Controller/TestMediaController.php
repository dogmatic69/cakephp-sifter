<?php
App::uses('Controller', 'Controller');

class TestMediaController extends Controller {

	public $uses = array('TestMedia');

	public $components = array('Sifter.Sifter', 'Paginator');

	public $redirecTest = null;

	public function redirect($url, $status = null, $exit = true) {
		return $this->redirecTest = compact('url', 'status', 'exit');
	}
}