<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\BaseController;

use Illuminate\Http\Request;

use App\Http\Models\Session as SessionModel;
use App\Http\Models\User as UserModel;

class SessionController extends BaseController {

	private $_model = null;

	public function __construct()
	{
		$this->_model	= new SessionModel();
	}

	public function create( Request $request )
	{

	}

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
