<?php

class GoogleAPI_Controller extends Controller {

	public $agentAccess = true;

	protected static $google_api_key;

	protected static $google_api_url;

	protected static $filter_by_country;

	static private $instance = null;

	static function set_google_api_key($key) {
		self::$google_api_key = $key;
	}

	static function get_google_api_key() {
		return self::$google_api_key;
	}

	static function set_google_api_url($url) {
		self::$google_api_url = $url;
	}

	static function get_google_api_url() {
		return self::$google_api_url;
	}

	static function set_filter_by_country($filter) {
		self::$filter_by_country = $filter;
	}

	static function get_filter_by_country() {
		return self::$filter_by_country;
	}

	static function inst() {
		if (!GoogleAPI_Controller::$instance) GoogleAPI_Controller::$instance = new GoogleAPI_Controller();
		return GoogleAPI_Controller::$instance;
	}

	function init(){
		parent::init();
	}

	/**
	 * this actually calls google api
	 *
	 * @param $queryString array, call params
	 * @param $api string, api name
	 * @param $call string, name of the method for this api
	 * @return json, with the api response body
	**/
	function callAPI($queryString = null, $api = 'place/', $call = null){
		
		$req = new RestfulService(self::get_google_api_url() . $api . $call . '/json');
		$req->setQueryString($queryString);		

		$response = $req->request();
		return json_decode($response->getBody());
		
	}

	function getSuggestions($request) {
		$postsVars = $request->postVars(); 
		$resp = $this->callAPI($this->getAutocompleteQueryString($postsVars['address']), 'place/', 'autocomplete');
		
		if(isset($resp->predictions) && is_array($resp->predictions) && !empty($resp->predictions)) {
			$html = "<ul>";
			foreach($resp->predictions as $desc) {
				$html .= "<li><a href='#' id='" . $desc->reference . "'>" . $desc->description . "</a></li>";
			}
			$html .= "</ul>";
		} else {
			$html = "<ul><li>NO RESULTS FOUND</li></ul>";
		}
		
		return $html;
	}

	function getCoordinates($request) {

		$postsVars = $request->postVars(); 

		$resp = $this->callAPI($this->getDetailsQueryString($postsVars['reference']), 'place/', 'details');
		
		$obj = new stdClass();
		$postcode = null;
		if($resp->result->geometry->location) {
			$obj->lat = $resp->result->geometry->location->lat;
			$obj->lng = $resp->result->geometry->location->lng;
			if(isset($resp->result->address_components) && is_array($resp->result->address_components)) {
				foreach($resp->result->address_components as $component) {
					if(is_array($component->types)) foreach($component->types as $type) {
						if(strpos($type,'postal_code') !== false) {
							$postcode = $component->long_name;
							continue;
						}
					}
				}
			}

			if($postcode) $obj->postcode = $postcode;
			return json_encode($obj);

		}
		
		return 'bad';
	}

	function getLocationCoordinate($location) {
		
		$resp = $this->callAPI($this->getAutocompleteQueryString($location), 'place/', 'autocomplete');
		
		if($resp && $resp->predictions) {
			$a = $resp->predictions[0];
			$point = $this->callAPI($this->getDetailsQueryString($a->reference), 'place/', 'details');
			return $point->result->geometry->location;
		}
		return false;
	}

	function getDistance($origin = null, $destination = null, $distance = null) {
		// http://maps.googleapis.com/maps/api/distancematrix/json?origins=41.43206,-81.38992&destinations=44.43206,-81.38992&mode=driving&units=imperial
		if(!$distance || !$origin || !$destination) return true;
		$resp = $this->callAPI(array('origins' => $origin, 'destinations' => $destination, 'mode' => 'driving', 'units' => 'imperial', 'sensor' => 'false'), '', 'distancematrix');
		
		if(isset($resp->rows) && is_array($resp->rows) && isset($resp->rows[0]->elements) && $resp->rows[0]->elements[0]->distance->value <= (int)$distance) return true;
		return false;
		
	}

	function getLocationFromAddress($request) {
		$postVars = $request->postVars(); 
		$address = explode(',', $postVars['address']);
		$resp = $this->getLocationCoordinate($address[0]);
		return json_encode($resp);
	}

	protected function getAutocompleteQueryString($details) {
		// https://maps.googleapis.com/maps/api/place/autocomplete/json?input=99%20courtenay%20place&sensor=true&types=geocode&components=country:nz&key=<apikey>
		$components = array(
			'input' => urlencode(trim($details)),
			'sensor' => 'true',
			'types' => 'geocode',
			'key' => self::get_google_api_key()
		);

		if(self::$filter_by_country) return array_merge(array('components' => 'country:'.self::get_filter_by_country()), $components);
		return $components;
	}

	protected function getDetailsQueryString($reference) {
		// https://maps.googleapis.com/maps/api/place/details/json?reference=CjQvAAAAQMCZbSAJ0UNuwhVMOW9zT9_xAppY7mu1WfhKLuXhSHa3NOAw7khXjEsZlrJwckk3EhB4xEIi-X-wkzFyuR6XxzkjGhTmSZ1kCOxUDXjrGIYhQoOGBMGbgA&sensor=true&key=<apikey>
		return array(
			'reference' => $reference,
			'key' => self::get_google_api_key(),
			'sensor' => 'true'
		);
	}

	protected function getDistanceQueryString() {

	}

}