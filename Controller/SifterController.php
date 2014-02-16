<?php
class SifterController extends SifterAppController {
	public $uses = false;

/**
 * Components to load 
 *
 * RequestHandler is required so we can take advantage of Cakes ext parse
 *
 * @var array
 */
	public $components = array(
		'RequestHandler',
		'Sifter.Sifter',
	);

	public function sift() {
	}

	public function admin_sift() {

	}
}