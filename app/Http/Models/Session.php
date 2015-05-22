<?php namespace App\Http\Models;

class Session extends Base
{

	private $_col 	= "sessions";

	// Create a user session record
	// We first check whether the user has an existing
	// session record, and if not, we then create a new
	// session record using the _insert method defined in
	// the Base class and return the $session object.

	public function create( $user )
	{
		$this->_where( 'user_id', ( string ) $user->id );
		$existing	= $this->_findOne( $this->_col );

		if ( !empty( ( array ) $existing ) )
		{
			$this->_where( 'user_id', ( string ) $user->id );
			$this->_remove( $this->col );
		}

		$session 			= new \stdClass();
		$session->user_id	= ( string ) $user->_id;
		$session->user_name	= $user->name;
		$session 			= $this->_insert( $this->_col, $session );

		return $session;
	}

	// Find an existing user record
	// We use the _findOne method defined in the Base class
	// and pass it our session token.

	public function find( $token )
	{
		$this->_where( 'id', $token );
		return $this->_findOne( $this->_col );
	}

	// Delete a user's session record
	// We use the _remove method defined in the Base class
	// and pass it our session token.  The job of verifying
	// whether the token exists in DB is left to the _remove
	// method.

	public function remove( $token )
	{
		$this->_where( '_id', $token );
		return $this->_remove( $this->_col );
	}
}