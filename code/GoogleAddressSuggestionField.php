<?php

class GoogleAddressSuggestionField extends FormField {


	protected $extraClasses = array('text');
	
	function __construct($name, $title = null, $value = "", $form = null){

		parent::__construct($name, $title, $value, $form);
	}

	function getTemplates() {
		return 'GoogleAddressSuggestionField';
	}

	function Field($properties = array()) {
		Requirements::javascript(GOOGLEADDRESSFIELD_DIR . '/javascript/googleMapsPointField.js');
		Requirements::javascript(GOOGLEADDRESSFIELD_DIR . '/javascript/googlePlaces.js');

		$obj = ($properties) ? $this->customise($properties) : $this;
		return $obj->renderWith($this->getTemplates());

	}

}