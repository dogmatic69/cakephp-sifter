<?php
App::uses('AppHelper', 'View/Helper');

class SifterHelper extends AppHelper {

	public $helpers = array(
		'Html',
	);

/**
 * Get the default model name
 *
 * @var string
 */
	public $defaultModel = null;

	public function beforeLayout($layoutFile) {
		parent::beforeLayout($layoutFile);

		$this->Html->script('Sifter.sifter', array(
			'inline' => false,
		));
	}

/**
 * Default Constructor
 *
 * @param View $View The View this helper is being attached to.
 * @param array $settings Configuration settings for the helper.
 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		$this->_defaultModel();
	}

/**
 * Get the name of the model being used
 *
 * @return string
 */
	public function getModel() {
		return $this->defaultModel;
	}

/**
 * Get the form fields
 *
 * @param string $model the model name to use (Plugin.Name format)
 *
 * @return array
 */
	public function getFields($model = null) {
		$fields = Hash::extract(self::_model($model)->sifterConfig('fields'), '{s}.input');
		foreach ($fields as &$config) {
			if (isset($config['options']['className'])) {
				unset($config['options']);
			}
		}
		return $fields;
	}

/**
 * Get a model instance
 *
 * @param string $model the name of the model to use
 *
 * @return Model
 */
	protected function _model($model) {
		return ClassRegistry::init($model ? $model : $this->defaultModel);
	}

/**
 * Figure out the model name and save the value
 *
 * @param string $model the name of the model (optional, blank for autodetect)
 *
 * @return string
 */
	protected function _defaultModel($model = null) {
		if (!empty($model)) {
			$this->defaultModel = $model;
			return $model;
		}

		if (!empty($this->request->params['models'])) {
			$model = implode('.', $this->request->params['models'][key($this->request->params['models'])]);
		}

		if (empty($model)) {
			pr($this);
		}
		$this->defaultModel = $model;
		return $model;
	}
}