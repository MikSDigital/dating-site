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

	//************  CREATE operations  ************//

	public function create( Request $request ) {}

	// To retrieve a record from the system, we
	// require that the user has an active session.
	// It doesn't have to match the id of the
	// retrieved user.  If the user doesn't have a
	// valid session, we return a PERMISSION_DENIED
	// error with a 403 status code.  Otherwise we
	// return the user record as a JSON object.

	//************  RETRIEVE operations  ************//

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

	// We start by fetching the request parameters,
	// the user's session token and distance
	// parameters in particular. This method,
	// however, does not requires an active session.
	// If a session is valid, we pass the user id to
	// the retrieve method of the UserModel class.

	// If a distance parameter is passed in, a
	// geospatial query is executed. If not, a
	// regular find query is performed. In case
	// of errors, we retrieve the error from the
	// model and return it to the user with a 403
	// status code. Otherwise, we return an array
	// containing the found users.

	public function retrieve( Request $request )
	{
		$token		= $request->get( 'token' );
		$distance	= $request->get( 'distance' );

		$session 	= $this->_check_session( $token );
		$result 	= $this->_model->retrieve( ( isset( $session->user_id ) ? $session->user_id : "" ), $distance, $request->get( 'limit' ), $request->get( 'page' ) );
		
		if ( !is_array( $result ) && !$result )
		{
			$result 		= new \stdClass();
			$result->error 	= $this->_model->get_error();
			$result->status = 403;
		}

		return $this->_response( $result );
	}
	
	//************  UPDATE operations  ************//
	
	public function update( Request $request, $id )
	{

	}

	//************  DELETE operations  ************//

	public function remove( Request $request, $id )
	{

	}

}
