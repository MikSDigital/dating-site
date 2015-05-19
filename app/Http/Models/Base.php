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

	private function _connect() {

		$conn = 'mongodb://'.$this->_config['host'];
		if(!empty($this->_config['port']))
		{
			$conn .= ":{$this->_config['port']}";
		}

		$options = array();
		if(!empty($this->_config['user']) && !empty($this->_config['pass']))
		{
			$options['username'] = $this->_config['user'];
			$options['password'] = $this->_config['pass'];
		}

		try
		{
			$this->_conn		= new \MongoClient( $conn, $options );

			$this->_db			= $this->_conn->{$this->_config['db']};

			return true;
		}
		catch( \MongoConnectionException $e )
		{
			$this->_conn		= null;

			return false;
		}

	}

}