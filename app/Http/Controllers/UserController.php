<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
 
class UserController extends Controller
{
    public function __construct()
    {
       $this->middleware('jwt.auth', ['except' => ['login']]);
    }
 
    public function login(Request $request)
    {
        // credenciales para loguear al usuario
       $credentials = request()->only('email', 'password');
       //dd($credentials);
    	try{
    		$token = JWTAuth::attempt($credentials);

    		if (!$token) {
    			# code...
    			return response()->json(['error'=>'invalid_credentials']);
    		}
    	}
    	catch(JWTException $e)
    	{
    		return response()->json(['error'=>'something_went_wrong'], 500);
    	}

    	return response()->json(['token' => $token], 200);
    }
}