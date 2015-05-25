<?php namespace App\Http\Models;

use App\Http\Models\Base as Model;

class User extends Model 
{
	private $_col	= "users";

	private $_error	= null;

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

	public function create( $user ) {}

	public function remove( $id ) {}

	public function retrieve( $id, $distance, $limit = 9999, $page = 1 ) {}

	public function update( $id, $data ) {}
}