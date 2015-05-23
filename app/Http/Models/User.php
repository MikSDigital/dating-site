<?php namespace App\Http\Models;

use App\Http\Models\Base as Model;

class User extends Model 
{
	private $_col	= "users";

	private $_error	= null;

	public function get( $where ) {}

	public function get_error() {}

	public function create( $user ) {}

	public function remove( $id ) {}

	public function retrieve( $id, $distance, $limit = 9999, $page = 1 ) {}

	public function update( $id, $data ) {}
}