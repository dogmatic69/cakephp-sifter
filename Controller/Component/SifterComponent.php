<?php
App::uses('Component', 'Controller');

class SifterComponent extends Component {

	public function initialize(Controller $Controller) {
		parent::initialize($Controller);
		if (!self::_isSiftable($Controller)) {
			return false;
		}

		if (empty($Controller->request->data['Sifter'])) {
			if (empty($Controller->request->params['named'])) {
				return true;
			}

			return self::_sift($Controller);
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
		} elseif ($Controller->request->is('post')) {
			unset($Controller->request->data['Sifter']);
			return $Controller->redirect(Hash::filter($Controller->request->data));
		}
	}

	protected function _sift(Controller $Controller) {
		if (empty($Controller->Paginator) || !$Controller->Paginator instanceof PaginatorComponent) {
			return false;
		}

		$filterFields = self::sift($Controller);
		if (empty($filterFields)) {
			return false;
		}

		$config = $Controller->{$Controller->modelClass}->sifterConfig('sifter');

		$contains = array();
		foreach ($filterFields as $field => $condition) {
			list($containModel) = pluginSplit($field);
			if ($containModel != $Controller->modelClass) {
				$contains[] = $containModel;
			}
		}

		$Controller->request->data = Hash::merge($Controller->request->params['named'], $Controller->request->data);

		return $Controller->Paginator->settings = array(
			'all',
			'conditions' => $filterFields,
			'contain' => array_unique(array_filter($contains)),
		);
	}

/**
 * Get conditions array based on the params passed in the GET request
 *
 * Searches through the data looking for fields that are configured in the model to be used for searching.
 *
 * @param Controller $Controller the controller instance
 *
 * @return array
 *
 * @throws CakeException when unknown operator is used
 */
	public function sift(Controller $Controller) {
		$fields = array_filter((array)$Controller->{$Controller->modelClass}->sifterConfig('fields'));
		$filterFields = array();
		foreach ($fields as $field => $config) {
			list($modelName, $fieldName) = pluginSplit($field);
			if (!empty($Controller->request->params['named'][$modelName][$fieldName . '_start']) && !empty($Controller->request->params['named'][$modelName][$fieldName . '_end'])) {
				$filterFields[$field . ' BETWEEN ? AND ?'] = array(
					$Controller->request->params['named'][$modelName][$fieldName . '_start'] . '00:00:00',
					$Controller->request->params['named'][$modelName][$fieldName . '_end'] . '23:59:59',
				);
				continue;
			}

			if (empty($Controller->request->params['named'][$modelName][$fieldName])) {
				continue;
			}

			switch (strtoupper($config['operator'])) {
				case 'IN':
				case '=':
					$filterFields[$field] = $Controller->request->params['named'][$modelName][$fieldName];
					break;

				case '!=':
				case '>=':
				case '<=':
					$filterFields[$field . ' ' . $config['operator']] = $Controller->request->params['named'][$modelName][$fieldName];
					break;

				case 'NOT LIKE':
				case 'LIKE':
					$filterFields[] = sprintf('%s %s "%%%s%%"', $field, strtoupper($config['operator']), $Controller->request->params['named'][$modelName][$fieldName]);
					break;

				default:
					throw new CakeException('Not sure what to do yet');
					break;
			}
		}

		return $filterFields;
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
		$Controller->helpers[] = 'Sifter.Sifter';
		if (!self::_isSiftable($Controller)) {
			$Controller->set('notSiftable', true);
			return true;
		}
		if (self::_isAjax($Controller)) {
			$Controller->viewClass = 'Json';
			$Controller->layout = 'ajax';
		}

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

		$Model = $Controller->{$Controller->modelClass};
		$config = $Model->sifterConfig('sifter');
		if (!in_array('Sifter', $Model->Behaviors->attached()) || !in_array($Controller->request->params['action'], $config['allowedMethods'])) {
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

		$Model = self::_model($Controller, $options['modelName']);
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

	protected function _model(Controller $Controller, $modelName) {
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