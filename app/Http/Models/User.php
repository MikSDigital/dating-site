<?php namespace App\Http\Models;

use App\Http\Models\Base as Model;

class User extends Model 
{
	private $_col	= "users";

	private $_error	= null;

	//************  CREATE operations  ************//

	// In this method, we make sure there isn't already
	// a user associated with the given email or mobile.
	// If that is true, we return the corresponding user.
	// If not, we delegate the task to create a user to
	// the _insert method of the Base class.
	// Before we return the user record, we cast the _id
	// to a string, as the object that is returned to
	// us defines the _id field as a MongoId object.
	// The client application, however, doesn't need
	// this object.

	public function create( $user )
	{
		if ( is_array( $user ) )
		{
			$user = ( object ) $user;
		}

		$this->_where( '$or', array(
				array(
					"email"		=> $user->email
					),
				array(
					"mobile"	=> $user->mobile
					)
				)
			);

		$existing = $this->_findOne( $this->_col );

		if ( empty( ( array ) $existing ) ) 
		{
			$user 	= $this->_insert( $this->_col, $user );
		}
		else
		{
			$user 	= $existing;
		}

		$user->_id 	= ( string ) $user->_id;

		return $user;
	}

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

	//************  UPDATE operations  ************//

	// As in the Base class, the update method accepts
	// both arrays and objects as the data for the user.
	// This makes the method much more flexible.
	// Before we update the user record, we make sure
	// the user's email and mobile aren't already in
	// use by another user.  If so, we set the error
	// EXISTING_USER and return false.  Otherwise, we
	// delegate the update operation to the Base class.

	public function update( $id, $data )
	{
		if ( is_array( $data ) )
		{
			$data = ( object ) $data;
		}

		if ( isset( $data->email ) || isset( $data->mobile ) )
		{
			$this->_where( '$and', array(
					array(
						'_id'		=> array( '$ne' => new \MongoId( $id ) )
						),
					array(
						'$or'		=> array(
							array(
								'email'		=> ( isset( $data->email ) ) ? $data->email : ""
								),
							array(
								'mobile'	=> ( isset( $data->mobile ) ) ? $data->mobile : ""
								)
							)
						)
				)
			);

			$existing = $this->_findOne( $this->_col );
			if ( !empty( ( array ) $existing ) && $existing->_id != $id )
			{
				$this->_error 	= "ERROR_EXISTING_USER";
				return false;
			}
		}

		$this->_where( '_id', $id );

		return $this->_update( $this->_col, ( array ) $data );
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

}