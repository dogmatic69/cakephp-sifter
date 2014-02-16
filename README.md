## Sifter

Sifter is a CakePHP plugin that makes searching and filtering data simple. It can also be configured to change how it searches.

All configuration is done in the model, which means there is no need to mess about with forms, redirects or any other code. A component interacts with request data and does various things depening on the data found in the request.

Main Features:

- Automatic form generation based on the configuration in the model / behavior config. (using included element)
- Field type introspection for best input type
- Automatic ajax autocomplete on all text inputs (requires jQuery to be included)
- Automatic select dropdown for foreign key fields (including deep relations, with customisable find method setting)
- Automatic pagination modification so it can be dropped in with minimal code changes, can also be used with normal finds or manually
- Post -> Redirect -> Get so searches are indexable, linkable
- Security - Will only search configured fields in configured actions, additional fields are simply ignored
- Deep relation search, especially with custom finds doing the search it is possible to control all your joins and additional search requirements


The plugin is made up of the following main sections:

### Behavior

The behavior does a number of things:
- Figure out the form inputs based on the field schema
- Find and call custom find methods in the model for fetching data
- Configure what is searched and where

### Component
- catch requests and either redirect POST -> GET or fetch data for ajax autocomplete
- fetch and set data for view variables to populate selects in the search form
- filter GET params and modify / return find conditions

### Helper / element

This is the frontend to the Sifter plugin. This is totaly optional but should cover most usage cases. If you are not going to use the included element for the search form you should at least look at if for ideas as to what is required to make the search work.

- Element: Search form default based on configured model and settings
- Helper: Convert behavior settings into Form inputs

## Install

Install with git

	git clone https://github.com/dogmatic69/cakephp-sifter Plugin/Sifter

Install as a submodule

	git submodule add https://github.com/dogmatic69/cakephp-sifter Plugin/Sifter

Install with composer (comming soon)

	composer.phar require dogmatic69/cakephp-sifter

## Basic usage

Attach the behavior to the model you would like to sift. This can be done in the actsAs property or dynamically, see the CakePHP docs for more details.

	class MyModel extends AppModel {
		public $actsAs = array(
			... other behaviors
			'Sifter.Sifter' => array(
				'fields' => array(
					'MyModel.field_1' => array(
						'input' => array(
							'placeholder' => 'Customize your form with ease',
						)
					),
					'MyOtherModel.field_2', 
					'field_3', // <- will automatically use the current model alias
				)
			),
		);

		// code
	}

Once its attached to the model, you need to attach the component to the controller so that it can catch and deal with sifter requests. Again see the CakePHP docs for specifics

class MyController extends AppController {
	
	public $components = array(
		...,
		'Sifter.Sifter',
	);

	// code
}

Finally you need to have a form so that users will be able to input the serch requirements. There is an included element which will build a generic search form based on the inputs that have been configured. Alternativly you could use the form helper to build your own.

	echo $this->element('Sifter.sifter');

That is it, now when you visit the page you should see a search form. Submitting data on in the form will do a PRG to to a URL containing all your params. If you are using pagination these will be included already in the `PaginatorComponent::$settings` property. If you are using a normal find on the page you can use the `SifterComponent::sift()` method to fetch the query conditions.

	$conditions = $this->Sifter->sift($this);
	pr($conditions);

	// output
	array(
		'conditions' => array(
			'MyModel.field_1' => 'foobar',
			'MyOtherModel.field_2' => 'baz',
			...
		),
		'contain' => array(
			'MyOtherModel',
		)
	)