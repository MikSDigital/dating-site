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

	// We first check for an existing user with the
	// passed in email or mobile.  If the user exists,
	// we verify that the given Facebook ID matches
	// the Facebook ID for the user record.  If that's
	// the case, we create the session object.  If it
	// isn't, the method returns a INVALID_CREDENTIALS
	// error with a 403 status code.

	public function create( Request $request )
	{
		$email 		= $request->get( 'email' );
		$mobile 	= $request->get( 'mobile' );
		$fbId		= $request->get( 'fbId' );

		$result		= new \stdClass();
		if ( ( empty( $email ) && empty( $mobile ) ) || empty( $fbId ) )
		{
			$result->error 	= "ERROR_INVALID_PARAMETERS";
			$result->status = 403;
		}
		else
		{
			$UserModel	= new UserModel();
			$where		= ( !empty( $email ) ) ? array( 'email' => $email ) : array( 'mobile' => $mobile );
			$user 		= $UserModel->get( $where );

			if ( empty( ( array ) $user ) )
			{
				$name 		= $request->get( 'name' );
				$gender		= $request->get( 'gender' );
				$location	= $request->get( 'location' );

				if ( empty( $name ) || empty( ( array ) $location ) || empty( $gender ) )
				{
					$result->error 	= "ERROR_INVALID_PARAMETERS";
					$result->status = 403;
				}
				else
				{
					if ( gettype( $location ) == "string" )
					{
						$location	= json_decode( $location );
					}

					$locObj 				= new \stdClass();
					$locObj->type 			= "Point";
					$locObj->coordinates 	= array( $location->lon, $location->lat );

					$user->name 	= $name;
					$user->fbId 	= $fbId;
					$user->email 	= $email;
					$user->mobile 	= $mobile;
					$user->gender 	= $gender;
					$user->location = $locObj;

					$user 			= $UserModel->create( $user );
				}
			}
			else
			{
				if ( $fbId != $user->fbId )
				{
					$result->error 	= "ERROR_INVALID_CREDENTIALS";
					$result->status = 403;
				}
			}

			if ( !property_exists( $result, "error" ) )
			{
				$result 		= $this->_model->create( $user );
				$result->token 	= $result->_id;
				unset( $result->_id );
			}
		}

		return $this->_response( $result );
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
