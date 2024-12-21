<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;

class AuthController extends BaseController
{
    public function signup(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8'
            ]
        );

        if ($validateUser->fails()) {
            return $this->sendError('Validation error', $validateUser->errors()->all(), 401);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return $this->sendResponse($user, 'User Created Successfully!');
    }

    public function login(Request $request)
    {

        $validateUser = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]
        );

        if ($validateUser->fails()) {

            return $this->sendError('Authentication Fails', $validateUser->errors()->all(), 404);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->sendError('Email not match!', 401);
        }
        $password = Hash::check($request->password, $user->password);
        if (!$password) {
            return $this->sendError('Password not match!', 401);
        }

        // dd($user, $password);
        // if (!$user) {
        //     return $this->sendError('Email & Password does not matched!', 401);
        // }
        // dd(Auth::attempt(['email' => $request->email, 'password' => Hash::make($request->password)]));
        // dd(Hash::check($request->password, $user->password));
        // dd($request->email, $request->password, $user, Auth::attempt(['email' => $request->email, 'password' => $request->pasword]));
        // dd(Hash::make('12345678')); 

        $userData = [
            'token' => $user->createToken('API TOKEN')->plainTextToken,
            'token_type' => 'bearer'
        ];
        return $this->sendResponse($userData, 'User Logged in Successfully!');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        // dd($user);
        if (!$user) {
            return $this->sendError('User not found', 401);
        }

        $user->tokens()->delete();



        return $this->sendResponse($user, 'User Looged Out Successfully!');
    }
}
