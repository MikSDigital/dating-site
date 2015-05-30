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

	// We first retrieve the necessary information
	// for the user and, as in the session creation
	// handler, we convert the user's location to the
	// appropriate format.

	// After this, we check that the required
	// information is passed in. Note that even though
	// both the email and mobile fields are optional,
	// at least one must be present.

	// After these checks, we invoke the create method
	// of the UserModel class to insert the new user
	// in the database. Finally, we return the new
	// user or an error.

	public function create( Request $request )
	{
		$email		= $request->get( 'email' );
		$fbId		= $request->get( 'fbId' );
		$gender		= $request->get( 'gender' );
		$location	= $request->get( 'location' );
		$mobile		= $request->get( 'mobile' );
		$name		= $request->get( 'name' );

		if ( gettype( $location ) == "string" )
		{
			$location	= json_decode( $location );
		}

		$locObj					= new \stdClass();
		$locObj->type 			= "Point";
		$locObj->coordinates	= array( $location->lon, $location->lat );
		
		$result 	= new \stdClass();
		if ( empty( $name ) || empty( ( array ) $location ) || empty ( $fbId ) || empty ( $gender ) || ( empty ( $email ) && empty ( $mobile ) ) )
		{
			$result->error 		= "ERROR_INVALID_PARAMETERS";
			$result->status 	= 403;
		}
		else
		{
			$user 	= array(
				"email"		=> $email,
				"fbId"		=> $fbId,
				"gender"	=> $gender,
				"location"	=> $locObj,
				"mobile"	=> $mobile,
				"name"		=> $name
			);

			$result = $this->_model->create( $user );
		}

		return $this->_response( $result );
	}

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
