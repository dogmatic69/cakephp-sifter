<?php
App::uses('ModelBehavior', 'Model');

/**
 * Sifter behavior
 *
 * This is for doing searches on models
 */

class SifterBehavior extends ModelBehavior {

/**
 * map methods for custom finds
 *
 * @var array
 */
	public $mapMethods = array(
		'/\b_findSifterDateTimeRange\b/' => 'findSifterDateTimeRange',
	);

/**
 * Behavior defaults
 *
 * allowedMethods: these are the methods that are allowed to search by default
 * requiresPost: requires POST to filter (must be false for PRG)
 *
 * @var array
 */
	protected $_defaults = array(
		'allowedMethods' => array('admin_index'),
		'requiresPost' => false,
	);

/**
 * Setup this behavior with the specified configuration settings.
 *
 * @param Model $model Model using this behavior
 * @param array $config Configuration settings for $model
 *
 * @return void
 */
	public function setup(Model $Model, $config = array()) {
		$config = array_merge(array('fields' => array()), $config);
		$Model->sifter = !empty($Model->sifter) ? $Model->sifter : array();
		$Model->sifter = Hash::merge($config['fields'], $Model->sifter);
		unset($config['fields']);

		$this->settings[$Model->alias] = Hash::merge(array('sifter' => $this->_defaults), array(
			'sifter' => array(
				'plugin' => $Model->plugin,
				'alias' => $Model->alias,
				'name' => $Model->name,
			),
			'fields' => $this->_formFields($Model),
		), array('sifter' => (array)$config));

		$Model->findMethods = array_merge($Model->findMethods, array(
			'sifterDateTimeRange' => true
		));

		return true;
	}

	public function findSifterDateTimeRange(Model $Model, $method, $state, $query, $results = array()) {
		if ($state == 'before') {

			return $query;
		}

		return $results;
	}

/**
 * Fetch config (optional by key)
 *
 * Returns the entire config, or the key specified
 *
 * @param Model $Model the model instance being used
 * @param string $key the key of the config to use
 *
 * @return array
 */
	public function sifterConfig(Model $Model, $key = null) {
		$config = $this->settings[$Model->alias];
		if ($key && array_key_exists($key, $config)) {
			return $config[$key];
		}
		return $config;
	}
/**
 * Figure out the form fields, merge configured settings with details extracted from the schema 
 *
 * @param Model $Model the model instance being used
 *
 * @return array
 */
	protected function _formFields(Model $Model) {
		if (empty($Model->sifter)) {
			return false;
		}

		$formatted = array();
		foreach ((array)$Model->sifter as $field => $config) {
			if (is_string($config) && is_int($field)) {
				$field = $config;
				$config = array();
			}
			$config = array('ownModel' => true, 'parentModel' => false);

			$alias = $Model->alias;
			if (strstr($field, '.') !== false) {
				list($alias, $field) = pluginSplit($field);
			}

			$arrayKey = implode('.', array($alias, $field));
			if ($Model->alias != $alias) {
				$config['ownModel'] = false;
				$config['parentModel'] = implode('.', array_filter(array($Model->plugin, $Model->alias)));

				if (isset($Model->$alias) && $Model->alias instanceof Model) {
					$formatted[$arrayKey] = $this->_fieldConfig($Model, $field, $config);
				} else {
					$formatted[$arrayKey] = $this->_fieldConfig(ClassRegistry::init($alias), $field, $config);
				}
			} else {
				$formatted[$arrayKey] = $this->_fieldConfig($Model, $field, $config);
			}

			$formatted[$arrayKey] = Hash::merge(array('input' => array('field' => $arrayKey)), $formatted[$arrayKey]);
			$formatted[$arrayKey]['viewVariable'] = $formatted[$arrayKey]['input']['type'] == 'select' ? Inflector::variable(Inflector::pluralize(self::_stripId($field))) : null;
		}

		return $formatted;
	}

/**
 * Figure out the config for a single field
 *
 * Config is made up of the following:
 * - Main array section is passed to Form->input() so uses the same params (sifter will figure out the best 
 * 		general settings based on the schema. Changes passed in will overload these guesses).
 * - sifter: This contains bits that are used for doing ajax calls to the correct place and other internal settings
 * 		required to make filtering work
 *
 * @param Model $Model the model instance being used
 * @param string $field the field to check
 * @param array $config the passed in config
 *
 * @return array
 */
	protected function _fieldConfig(Model $Model, $field, $config) {
		if (!$Model->Behaviors->attached('Sifter')) {
			$Model->Behaviors->attach('Sifter.Sifter');
		}

		$friendlyName = $this->_friendlyName($field);
		$friendlyField = __d(Inflector::underscore($Model->plugin) ?: 'sifter', $friendlyName);

		if ($config['ownModel']) {
			$placeholder = __d('sifter', 'Search by %s', $friendlyField);
			$label = $friendlyName;
		} else {
			$alias = $this->_friendlyName($Model->alias);
			$placeholder = __d('sifter', 'Search by %s %s', __d(Inflector::underscore($Model->plugin) ?: 'sifter', $alias), $friendlyField);
			$label = sprintf('%s %s', $alias, $friendlyName);
		}
		$config = Hash::merge($this->_inputConfig($Model, $field), array(
			'input' => array(
				'label' => $label,
				'placeholder' => $placeholder,
				'required' => false,
			)
		), $config);

		return $config;
	}

/**
 * Wrapper method to break apart the config building for various field types
 *
 * @param Model $Model the model instance being used
 * @param string $field the field being checked (can be Model.field / field)
 *
 * @return array
 */
	protected function _inputConfig(Model $Model, $field) {
		$schema = $Model->schema($field);

		$sifter = array('fieldType' => $schema['type']);
		if (self::_isForeignKeyField($field)) {
			$schema['type'] = 'foreignKey';
		}

		switch ($schema['type']) {
			case 'text':
			case 'string':
				$schema['type'] = 'text';
				break;

			case 'datetime':
			case 'foreignKey':
			case 'float':
			case 'boolean':
				break;

			default:
				var_dump('Field:');
				var_dump($schema);
				exit;
		}

		$fieldType = '_fieldType' . ucfirst($schema['type']);

		return Hash::merge($sifter, $this->{$fieldType}($Model, $field));
	}

/**
 * Sifter config for text fields
 *
 * @param Model $Model the model being used
 * @param string $field the field being used
 *
 * @return array
 */
	protected function _fieldTypeText(Model $Model, $field) {
		return array(
			'input' => array(
				'type' => 'text',
			),
			'operator' => 'LIKE',
		);
	}

/**
 * Sifter config for foreign keys
 *
 * @param Model $Model the model being used
 * @param string $field the field being used
 *
 * @return array
 */
	protected function _fieldTypeForeignKey(Model $Model, $field) {
		$method = 'sifter' . Inflector::camelize(Inflector::pluralize(self::_stripId($field)));

		if (array_key_exists($method, $Model->findMethods) && $Model->findMethods[$method] === true) {
			$methodType = 'customFind';
		} elseif ($Model->hasMethod($method)) {
			$methodType = 'method';
		} else {
			$method = 'list';
			$methodType = 'customFind';
		}

		return array(
			'input' => array(
				'label' => __d('sifter', 'Filter by %s', __d('sifter', self::_friendlyName($field))),
				'type' => 'select',
				'multiple' => true,
				'empty' => false,
				'options' => array(
					'className' => $this->_relatedModelName($Model, $field),
					'method' => $method,
					'methodType' => $methodType,
				),
			),
			'operator' => 'IN',
		);
	}

/**
 * Get related model name
 *
 * @param Model $Model the model to check
 * @param string $field the field name (fk)
 *
 * @return null|string
 *
 * @throws InvalidArgumentException when relation can not be found
 */
	protected function _relatedModelName(Model $Model, $field) {
		if (empty($Model->belongsTo)) {
			return null;
		}

		foreach ($Model->belongsTo as $alias => $config) {
			if ($config['foreignKey'] == $field) {
				return implode('.', array($Model->{$alias}->plugin, $Model->{$alias}->name));
			}
		}

		throw new InvalidArgumentException(sprintf('Unable to find the related model for "%s"', $field));
	}

/**
 * Sifter config for floats
 *
 * @param Model $Model the model being used
 * @param string $field the field being used
 *
 * @return array
 */
	protected function _fieldTypeFloat(Model $Model, $field) {
		$schema = $Model->schema($field);
		list($ms, $ls) = explode(',', $schema['length']);
		$max = ($ms - $ls) * str_pad(1, $ms - $ls, '0') - 1;

		return array(
			'input' => array(
				'type' => 'number',
				'min' => -$max,
				'max' => $max,
				'step' => 1 / (float)str_pad(1, $ls + 1, '0'),
			),
			'operator' => 'BETWEEN',
		);
	}

/**
 * Sifter config for boolean
 *
 * @param Model $Model the model being used
 * @param string $field the field being used
 *
 * @return array
 */
	protected function _fieldTypeBoolean(Model $Model, $field) {
		return array(
			'input' => array(
				'type' => 'select',
				'options' => array(
					1 => __d('sifter', 'True / Active'),
					0 => __d('sifter', 'False / Inactive'),
				),
				'empty' => __d('sifter', 'All records'),
			),
			'operator' => '=',
		);
	}

	protected function _fieldTypeDatetime(Model $Model, $field) {
		$Model->virtualFields['oldest'] = sprintf('MIN(%s.%s)', $Model->alias, $field);
		$Model->virtualFields['newest'] = sprintf('MAX(%s.%s)', $Model->alias, $field);
		$range = $Model->find('first', array(
			'fields' => array(
				$Model->alias . '.oldest',
				$Model->alias . '.newest',
			),
			'conditions' => array(
				array($Model->alias . '.' . $field . ' !=' => null),
				array($Model->alias . '.' . $field . ' !=' => ''),
				array($Model->alias . '.' . $field . ' !=' => '0000-00-00 00:00:00'),
			),
		));
		$range = array_merge(array(
			'oldest' => date('Y-m-d H:i:s', 0),
			'newest' => date('Y-m-d H:i:s'),
		), !empty($range[$Model->alias]) ? $range[$Model->alias] : array());

		return array(
			'input' => array(
				'type' => 'text',
				'class' => 'datetime',
				'data-oldest' => $range['oldest'],
				'data-newest' => $range['newest'],
			),
			'operator' => 'BETWEEN',
		);
	}

/**
 * figure out a friendly name
 *
 * @param string $field the field being used
 *
 * @return array
 */
	protected function _friendlyName($field) {
		if (self::_isForeignKeyField($field)) {
			$field = self::_stripId($field);
		}

		return Inflector::humanize($field);
	}

/**
 * figure out if the field is a foreign key
 *
 * @param string $field the field being used
 *
 * @return array
 */
	protected function _isForeignKeyField($field) {
		return substr($field, -3) == '_id';
	}

/**
 * remove the _id part from a field name
 *
 * @param string $field the field being used
 *
 * @return array
 */
	protected function _stripId($field) {
		if (!self::_isForeignKeyField($field)) {
			return $field;
		}
		return substr($field, 0, -3);
	}
}