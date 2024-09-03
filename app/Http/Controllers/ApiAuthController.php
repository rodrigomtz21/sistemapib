<?php

namespace App\Http\Controllers;


use JWTAuth;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class ApiAuthController extends Controller
{
    //
    public function authenticate()
    {
    	//get user data
    	//check if user credentials are correct
    	//generate token
    	$credentials = request()->only('email', 'password');

    	try{
    		$token = JWTAuth::attempt($credentials);

    		if (!$token) {
    			# code...
    			return response()->json(['error'=>'invalid_credentials'], 401);
    		}
    	}
    	catch(JWTException $e)
    	{
    		return response()->json(['error'=>'something_went_wrong'], 500);
    	}

    	return response()->json(['token' => $token], 200);

    }

    public function register()
    {
    	$email = request()->email;
    	$name = request()->name;
    	$password = request()->password;

    	$user = User::create([
    		'name' => $name,
    		'email' => $email,
    		'password' => bcrypt($password)
    	]);

    	$token = JWTAuth::fromUser($user);

    	return response()->json(['token' => $token], 200);
    }
}
