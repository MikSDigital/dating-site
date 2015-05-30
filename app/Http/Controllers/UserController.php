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
