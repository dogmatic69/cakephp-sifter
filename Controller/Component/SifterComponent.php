<?php
App::uses('Component', 'Controller');

class SifterComponent extends Component {

	public function initialize(Controller $Controller) {
		parent::initialize($Controller);
		$dealWithRequest = $Controller->request->is('post') && !empty($Controller->request->data['Sifter']) && self::_isSiftable($Controller);

		if (!$dealWithRequest) {
			return true;
		}

		$Model = $Controller->{$Controller->modelClass};
		$field = array_filter((array)$Model->sifterConfig('fields'));

		if (self::_isAjax($Controller)) {
			if (!array_key_exists($Controller->request->data['Sifter']['search_field'], $field) || empty($Controller->request->data['Sifter']['search_value'])) {
				return true;
			}

			return self::_setAutoComplete($Controller, array(
				$Controller->request->data['Sifter']['search_field'] => $field[$Controller->request->data['Sifter']['search_field']]
			));
		}

		return self::_prg($Controller, $field);
	}

	protected function _prg(Controller $Controller, $fields) {
		
	}

	protected function _setAutoComplete(Controller $Controller, array $field) {
		$fieldName = current(array_keys($field));
		$fieldConfig = current(array_values($field));

		$data = self::_autocompleteSearch($Controller, array(
			'modelName' => $fieldConfig['ownModel'] ? null : current(pluginSplit($fieldName)),
			'pass' => array(
				'conditions' => array(
					sprintf('%s LIKE "%%%s%%"', $fieldName, $Controller->request->data['Sifter']['search_value']),
				),
				'fields' => array($fieldName),
			),
		));
		$sifter = array(
			'error' => false,
			'message' => false,
			'data' => $data,
		);
		if (empty($data)) {
			$sifter = array(
				'error' => true,
				'message' => __d('sifter', 'No data found for the selected term'),
				'data' => null,
			);
		}

		$Controller->set(array(
			'sifter' => $sifter,
			'_serialize' => 'sifter',
		));

		return true;
	}

/**
 * BeforeRender Callback
 *
 * The before render callback is used to load up any data that is requrired for the
 * search forms (eg: selects). This is populated with information based on what was configured
 * or determined by the behavior
 *
 * @param Controller $Controller the controller instance that is loaded
 *
 * @return boolean
 */
	public function beforeRender(Controller $Controller) {
		if (!self::_isSiftable($Controller)) {
			return true;
		}
		if (self::_isAjax($Controller)) {
			$Controller->viewClass = 'Json';
			$Controller->layout = 'ajax';
		}
		$Controller->helpers[] = 'Sifter.Sifter';

		$Model = $Controller->{$Controller->modelClass};
		$fields = array_filter((array)$Model->sifterConfig('fields'));
		foreach ($fields as $field => $config) {
			$var = $this->_fetchData($Controller, $config);
			
			if ($var) {
				$Controller->set($config['viewVariable'], $var);
			}
		}

		return true;
	}

	protected function _isSiftable(Controller $Controller) {
		if (empty($Controller->{$Controller->modelClass})) {
			return false;
		}

		if (!in_array('Sifter', $Controller->{$Controller->modelClass}->Behaviors->attached())) {
			return false;
		}

		return true;
	}

	protected function _isAjax(Controller $Controller) {
		return !empty($Controller->request->params['ext']) && $Controller->request->params['ext'] == 'json';
	}

/**
 * fetch data based on the available config
 *
 * Currently this is configured to work with selects only.
 *
 * @param array $fieldConfig the configuration for the field
 *
 * @return array
 * 
 * @throws InvalidArgumentException when data cant be fetched
 */
	protected function _fetchData(Controller $Controller, array $fieldConfig, $pass = null) {
		if ($fieldConfig['input']['type'] != 'select') {
			return;
		}

		if (!array_key_exists('className', $fieldConfig['input']['options'])) {
			return;
		}


		return self::_autocompleteSearch($Controller, array(
			'modelName' => $fieldConfig['input']['options']['className'],
			'methodType' => $fieldConfig['input']['options']['methodType'],
			'method' => $fieldConfig['input']['options']['method'],
			'pass' => $pass,
		));
	}

	protected function _autocompleteSearch($Controller, array $options = array()) {
		$options = Hash::merge(array(
			'methodType' => 'customFind',
			'method' => 'list',
			'modelName' => null,
			'pass' => null,
		), $options);

		$Model = self::_Model($Controller, $options['modelName']);
		switch ($options['methodType']) {
			case 'customFind':
				if ($options['pass']) {
					return $Model->find($options['method'], $options['pass']);
				} else {
					return $Model->find($options['method']);
				}
				break;

			case 'method':
				if ($options['pass']) {
					return $Model->{$options['method']}($options['pass']);
				} else {
					return $Model->{$options['method']}();
				}
				break;
		}

		throw new InvalidArgumentException('Unable to fetch data');
	}

	protected function _Model(Controller $Controller, $modelName) {
		if ($modelName instanceof Model) {
			return $modelName;
		}

		if ($modelName === null) {
			$modelName = $Controller->modelClass;
		}

		if (!empty($Controller->{$modelName}) && $Controller->{$modelName} instanceof Model) {
			return $Controller->{$modelName};
		}

		if (!empty($Controller->{$Controller->modelClass}->{$modelName}) && $Controller->{$Controller->modelClass}->{$modelName} instanceof Model) {
			return $Controller->{$Controller->modelClass}->{$modelName};
		}

		return ClassRegistry::init($modelName);
	}
}