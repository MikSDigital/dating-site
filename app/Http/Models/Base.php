<?php namespace App\Http\Models;

/* Database Connection 						  */
/* This class will perform queries to MongoDB */
/* using ActiveRecord-like syntax.			  */

use Illuminate\Support\Facades\Config;

class Base
{

	private $_config		= null;

	private $_conn			= null;

	private $_db			= null;

	// The following are holders for the *where*,
	// *select*, *limit* and *offset* of the DB queries.

	private $_ws			= array();

	private $_sls			= array();

	private $_lmt			= 99999;

	private $_ost			= 0;

	// The _limit method will be useful for paginating the
	// results of a READ operation.  The user can set the
	// $limit param to specify the number of records to
	// retrieve and optionally an $offset param to specify
	// the page to read from.

	protected function _limit( $limit, $offset = null )
	{
		if ( $limit !== NULL && is_numeric( $limit ) && $limit >= 1 )
		{
			$this->_lmt = $limit;
		}
		if ( $offset !== NULL && is_numeric( $offset ) && $offset >= 1 )
		{
			$this->_ost = $offset;
		}

	}

	// The _select method will be used to determine which
	// fields of a record a READ query must return.  The
	// select statement must be provided as a comma-separated
	// string.

	protected function _select( $select = "" )
	{
		$fields = explode( ',', $select );
		foreach ( $fields as $field )
		{
			$this->_sls[trim( $field )] = true;
		}
	}

	// The _where method will be used to filter the query
	// results and can either be an array or a key/value
	// pair.

	protected function _where( $key, $value = null )
	{
		if ( is_array( $key ) )
		{
			foreach( $key as $k => $v )
			{
				$this->_ws[$k] = $v;
			}
			else
			{
				$this->_ws[$key] = $value;
			}
		}
	}

	public function __construct()
	{
		
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

	// _set_where will check if the $where param is an
	// array, and if so, it combines the given values with
	// the existing ones using the _where helper method.

	private function _set_where( $where = null )
	{
		if ( is_array( $where ) )
		{
			$where = array_merge( $where, $this->_ws );
			foreach( $where as $k => $v )
			{
				if ( $k == "_id" && ( gettype( $v ) == "string" ) )
				{
					$this->_ws[$k] = new \MongoId( $v );
				}
				else
				{
					$this->_ws[$k] = $v;
				}
			}
		}
		else if ( is_string( $where ) )
		{
			$wheres = explode( ',', $where );
			foreach ( $wheres as $wr )
			{
				$pair = explode( '=', trim( $wr ) );
				if ( $pair[0] == "_id" )
				{
					$this->_ws[trim( $pair[0] )] = new \MongoId( trim( $pair[1] ) );
				}
				else
				{
					$this->_ws[trim( $pair[0] )] = trim( $pair[1] );
				}
			}
		}
	}

	// _flush method will reset all the query parameters
	// once the query has been performed.

	private function _flush()
	{
		$this->_ws			= array();
		$this->_sls			= array();
		$this->_lmt			= 99999;
		$this->_ost			= 0;
	}

	// Our _insert method will provide the CREATE operation
	// from the MongoDB interface.
	// Even though the PHP driver expects the inserted data
	// to be an array, our class will support both arrays
	// and objects.

	protected function _insert( $collection, $data )
	{
		if( is_object( $data ) )
		{
			$data = ( array ) $data;
		}

		$result = false;

		try
		{
			if( $this->_db->{$collection}->insert( $data ) )
			{
				$data['_id']		= ( string ) $data['_id'];
				$result				= ( object ) $data;
			}
		}
		catch
		{
			$result 				= new \stdClass();
			$result->error 			= $e->getMessage();
		}

		$this->_flush();

		return $result;
	}

	// We're going to implement two READ methods.  One will be
	// used to retrieve a single record while the other will be
	// used to fetch a list of records.

	protected function _findOne( $collection, $where = array() )
	{
		$this->_set_where( $where );

		$row	= $this->_db->{$collection}->findOne( $this->_ws, $this->_sls );
		$this->_flush();
		
		return ( object ) $row;
	}

	protected function _find( $collection, $where = array() )
	{
		$this->_set_where( $where );

		$docs = $this->_db->{$collection}
			->find( $this->_ws, $this->_sls )
			->limit( $this->_lmt )
			->skip( $this->_ost );
		$this->_flush();

		$result = array();
		
		foreach( $docs as $row )
		{
			$result[] = ( object ) $row;
		}

		return $result;
	}

	// UPDATE method to edit or modify a record's data

	protected function _update( $collection, $data, $where = array() )
	{
		if ( is_object( $data ) )
		{
			$daa = ( array ) $data;
		}

		$this->_set_where( $where );

		if ( array_key_exists( '$set', $data) )
		{
			$newdoc = $data;
		}
		else
		{
			$newdoc = array( '$set' => $data );
		}

		$result = false;

		try
		{
			if ( $this->_db->{$collection}->update( $this->_ws, $newdoc ) )
				$result = ( object ) $data;
		}
		catch ( \MongoCursorException $e )
		{
			$result 			= new \stdClass();
			$result->error 		= $e->getMessage();
		}

		$this->_flush();

		return $result;
	}

}