<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\BaseController;

use Illuminate\Http\Request;

use App\Http\Models\Session as SessionModel;
use App\Http\Models\User as UserModel;

class SessionController extends BaseController {

	private $_model = null;

	// We set the controller model's object in the
	// constructor and declare a couple of methods,
	// which are the actions supported by the
	// Sessions resource.

	public function __construct()
	{
		$this->_model	= new SessionModel();
	}

	public function create( Request $request )
	{

	}

	// The destroy method uses the SessionModel's
	// remove method and returns the result using
	// the _response method of the BaseController
	// class.  If removing the session is
	// successful, we return an empty object. If
	// an error occurred, we return an error with
	// a 403 status code.

	public function destroy( $token )
	{
		$result = new \stdClass();

		if ( $this->_model->remove( $token ) )
		{
			$result->error 	= "ERROR_REMOVING_SESSION";
			$result->status = 403;
		}

		return $this->_response( $result );
	}

}
