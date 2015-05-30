<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\BaseController;

use Illuminate\Http\Request;

use App\Http\Models\User as UserModel;

class UserController extends Controller {

	private $_model	= null;

	public function __construct()
	{
		$this->_model	= new UserModel();
	}

	public function create( Request $request ) {}

	// To retrieve a record from the system, we
	// require that the user has an active session.
	// It doesn't have to match the id of the
	// retrieved user.  If the user doesn't have a
	// valid session, we return a PERMISSION_DENIED
	// error with a 403 status code.  Otherwise we
	// return the user record as a JSON object.

	public function get( Request $request, $id )
	{
		$token 	= $request->get( 'token' );

		$result = new \stdClass();
		if ( !$this->_check_session( $token ) )
		{
			$result->error 	= "PERMISSION_DENIED";
			$result->status = 403;
		}
		else
		{
			$result = $this->_model->get( $id );
		}

		return $this->_response( $result );
	}
	
	public function remove( Request $request, $id ) {}
	
	public function retrieve( Request $request ) {}
	
	public function update( Request $request, $id ) {}

}
