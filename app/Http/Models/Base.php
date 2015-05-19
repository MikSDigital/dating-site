<?php namespace App\Http\Models;

/* Database Connection 						  */
/* This class will perform queries to MongoDB */
/* using ActiveRecord-like syntax.			  */

use Illuminate\Support\Facades\Config;

class Base {

	private $_config		= null;

	private $_conn			= null;

	private $_db			= null;

	public function __construct() {
		
		$this->_config		= Config::get( 'mongodb' );

		$this->_connect();
	}

	private function _connect() {}
}