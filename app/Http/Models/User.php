<?php namespace App\Http\Models;

use App\Http\Models\Base as Model;

class User extends Model 
{
	private $_col	= "users";

	private $_error	= null;

	//************  READ operations  ************//

	// We check if the $where parameter being passed
	// is an array.  If not, we assume it's a user ID
	// and pass the information to the _findOne method.

	public function get( $where )
	{
		if ( is_array( $where ) )
		{
			return $this->_findOne( $this->_col, $where );
		}
		else
		{
			$this->_where( '_id', $where );
			return $this->_findOne( $this->_col );
		}
	}

	// get_error is a helper method that will provide
	// the controller with more information about
	// failure in the model.

	public function get_error()
	{
		return this->error;
	}

	// The retrieve method will fetch a list of users.
	// It supports pagination and geospatial queries.
	// If the _id and distance parameters are passed,
	// it attempts to search for nearby users based
	// on location.
	// If _id does not match a record, it returns false.
	// If the user exists, it prepares a geospatial
	// query using a MongoDB 2dsphere index. 

	public function retrieve( $id, $distance, $limit = 9999, $page = 1 )
	{
		if ( !empty( $id ) && !empty( $distance ) )
		{
			$this->_where( '_id', $id );
			$this->_select( 'location' );
			$user = $this->_findOne( $this->_col );

			if ( empty( ( array ) $user ) )
			{
				$this->_error = "ERROR_INVALID_USER";
				return false;
			}

			$this->_where( '$and', array(
					array(
						'_id'		=>	array( '$ne' => new \MongoId( $id ) )
						),
					array(
						'location'	=> array(
							'$nearSphere'		=> array(
								'$geometry'		=> array(
									'type'			=> "Point",
									'coordinates'	=> $user->location['coordinates']
									),
								'$maxDistance'	=> ( float ) $distance
								)
							)
						)
				) );
		}

		$this->_limit( $limit, ( $limit * --$page ) );

		return $this->_find( $this->_col );
	}


	//************  CREATE operations  ************//

	public function create( $user )
	{

	}

	//************  DELETE operations  ************//

	// remove method for deleting users.
	// We check that the given _id corresponds to an
	// existing user and attempt to remove it using
	// the _remove method of the Base class.
	// If something goes wrong, we set the model's
	// _error property and return false.

	public function remove( $id )
	{
		$this->_where( '_id', $id );
		$user = $this->_findOne( $this->_col );

		if ( empty( ( array ) $user ) )
		{
			$this->_error		= "ERROR_INVALID_ID";
			return false;
		}
		else
		{
			$this->_where( '_id', $id );
			if ( !$this->_remove( $this->_col ) )
			{
				$this->_error	= "ERROR_REMOVING_USER";
				return false;
			}
		}

		return $user;
	}

	//************  UPDATE operations  ************//

	public function update( $id, $data )
	{

	}
}