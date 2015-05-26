<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

// We will need to access the Session model,
// so we declare it in the class
use App\Http\Models\Session as SessionModel;

class BaseController extends Controller {

	// The _check_session method is used to verify the
	// session token, which is passed as the first argument.
	// Some tasks require the user to be logged in, such as
	// when updating a user record, the user corresponding
	// with the active session needs to match the _id of
	// the record that needs to be updated.
	// We fetch the session for the session token and if
	// the id of the user that corresponds with the session
	// matches the id that is passed in the second argument,
	// we return the session.  Otherwise, we return false.

	protected function _check_session( $token = "", $id = "" )
	{
		$result = false;

		if ( !empty( $token ) )
		{
			$SessionModel	= new SessionModel();
			$session 		= $SessionModel->find( $token );

			if ( !empty( ( array ) $session ) )
			{
				if( !empty( $id ) )
				{
					if ( $session->user_id == $id )
					{
						$result	= $session;
					}
				}
				else
				{
					$result		= $session;
				}
			}
		}

		return $result;
	}

	// The _response helper method takes care of sending
	// a result back to the client that consumes the API.
	// At the moment, we only support JSON.  If the
	// result to return is an object and has a status
	// parameter, we set it using Laravel's response
	// helper method.  Otherwise, we simply return the
	// result.

	protected function _response( $result )
	{
		if ( is_object( $result ) && property_exists( $result, "status" ) )
		{
			return response()->json( $result, $result->status );
		}
		else
		{
			return response()->json( $result );
		}
	}
}
