<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessTokenFactory;


class UserController extends Controller
{


    public function register(RegisterUser $request)
    {
        try {
            // Validation is already handled by the form request (RegisterUser)

            // Create a new user
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->first_name = $request->first_name;

            $user->password = bcrypt($request->password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json([
                'success' => false,
                'message' => 'Error creating user',
                'error' => $e->getMessage(),
            ]);
        }



    }


    public function login(LoginUserRequest $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to log the user in
        if (auth()->attempt($request->only('email', 'password'))) {
            // The user is logged in
            $user = auth()->user();
            // dd($user);
            $token = $user->createToken('token-name')->plainTextToken;


            return response()->json([
                'success' => true,
                'message' => 'User logged in successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        }

        // The user is not logged in
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

}
