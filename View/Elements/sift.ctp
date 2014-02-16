<?php
if (isset($notSiftable) && $notSiftable === true) {
	if (Configure::read('debug')) {
		echo $this->Html->tag('p', __d('sifter', 'Sifter is not enabled, make sure to include the correct actions'), array(
			'class' => 'danger',
		));
	}
	return;
}
$model = $this->Sifter->getModel();;
$fields = $this->Sifter->getFields();

echo $this->Form->create(null, array(
	'class' => 'sifter-form form-inline',
	'data-ajax-source' => Router::url(array('ext' => 'json')),
	'data-ajax-min-length' => 2,
	'inputDefaults' => array(
		'div' => 'form-group',
		'class' => 'form-control',
	),
	'novalidate' => 'novalidate'
));
	echo $this->Form->hidden('Sifter.location', array('value' => $this->request->here));
	echo $this->Form->hidden('Sifter.search_field');
	echo $this->Form->hidden('Sifter.search_value');
	foreach ($fields as $options) {
		$options['data-field'] = $options['field'];
		unset($options['field']);

		if ($options['type'] == 'text' && !empty($options['class']) && $options['class'] == 'datetime') {
			$start = $end = array_merge($options, array('div' => false, 'label' => false));
			$start['class'] .= ' start';
			$end['class'] .= ' end';

			$start['placeholder'] = __d('sifter', 'Start date');
			$end['placeholder'] = __d('sifter', 'End date');
			echo $this->Html->tag('div', implode('', array(
				$this->Form->label($options['data-field']),
				$this->Form->input($options['data-field'] . '_start', $start),
				$this->Form->input($options['data-field'] . '_end', $end)
			)), array('class' => 'form-group'));
		} else {
			echo $this->Form->input($options['data-field'], $options);
		}
	}
	echo $this->Form->submit(__d('sifter', 'Sift records'));
echo $this->Form->end();