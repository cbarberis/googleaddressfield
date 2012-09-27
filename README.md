googleaddressfield
==================

# googleaddressfield Module

## Overview

Simple module that returns address where you start typing. It uses Google Places API.

## Maintainer Contact

* Carlos Barberis (carlos at silverstripe dot com)

## Requirements

* Silverstripe 3.0.1 or newer

## Module Status

Still under active development. The idea is to integrate this module with SilverStripe-GoogleMap (SS2.4 compatible).

## Installation

* Install the module code in the project root (folder must be called 'googleaddressfield') and flush=all
* set Google Places API credentials
GoogleAPI_Controller::set_google_api_key('your_api_key');
GoogleAPI_Controller::set_filter_by_country('nz'); // this is optional, set it if you want to filter suggestions by country
* Use it in getCMSFields, the DO needs to have 'Lat' and 'Lon' as DB properties, the module looks for these properties to save the geolocation (lon,lat).
* You can save postcode too (same as above) the DO needs a 'Postcode' property.

## Usage

* $fields->addFieldToTab('Root.Main', new GoogleAddressSuggestionField('Address', 'Address'));
The Field needs to be called 'Address', I may change this to something more flexible soon. 
