<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessTokenFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{


    public function register(RegisterUser $request)
    {
        try {

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->first_name = $request->first_name;
            $user->password = bcrypt($request->password);
            $user->save();


            Log::info('User created successfully', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating user', ['error_message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating user',
                'error' => $e->getMessage(),
            ]);
        }
    }



    public function login(LoginUserRequest $request)
    {
        try {
            // Validate the request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Attempt to log the user in
            if (auth()->attempt($request->only('email', 'password'))) {
                // The user is logged in
                $user = auth()->user();
                $token = $user->createToken('token-name')->plainTextToken;

                // Log successful login
                Log::info('User logged in successfully', ['user_id' => $user->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'User logged in successfully',
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                    ],
                ]);
            }

            // Log unsuccessful login
            Log::warning('Invalid login attempt', ['email' => $request->email]);

            // The user is not logged in
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        } catch (\Exception $e) {
            // Handle the exception
            Log::error('Error during login', ['error_message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error during login',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->tokens()->delete();

            // Log successful logout
            Log::info('User logged out successfully', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully',
            ]);
        } catch (\Exception $e) {
            // Handle the exception
            Log::error('Error during logout', ['error_message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error during logout',
                'error' => $e->getMessage(),
            ]);
        }
    }



}
