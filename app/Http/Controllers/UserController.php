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

	public function get( Request $request, $id ) {}
	
	public function remove( Request $request, $id ) {}
	
	public function retrieve( Request $request ) {}
	
	public function update( Request $request, $id ) {}

}
