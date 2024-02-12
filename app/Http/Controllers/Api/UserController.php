<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUser;
use App\Mail\SendCodeResetPassword;
use App\Models\ResetCodePassword;
use App\Models\ResetCodePassword2;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessTokenFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
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

    public function checkCode(Request $request)
    {
        try {
            // find the code
            $passwordReset = ResetCodePassword2::firstWhere('code', $request->code);

            // check if the code exists
            if (!$passwordReset) {
                return response(['message' => trans('passwords.code_not_found')], 404);
            }

            // check if it does not expire: the time is one hour
            if ($passwordReset->created_at > now()->addHour()) {
                $passwordReset->delete();
                return response(['message' => trans('passwords.code_is_expire')], 422);
            }

            return response([
                'code' => $passwordReset->code,
                'message' => trans('passwords.code_is_valid')
            ], 200);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function reset(Request $request)
    {
        try {
            // find the code
            $passwordReset = ResetCodePassword2::firstWhere('code', $request->code);

            // check if the code exists
            if (!$passwordReset) {
                return response(['message' => trans('passwords.code_not_found')], 404);
            }

            // check if it does not expire: the time is one hour
            if ($passwordReset->created_at > now()->addHour()) {
                $passwordReset->delete();
                return response(['message' => trans('passwords.code_is_expire')], 422);
            }

            // find user's email
            $user = User::firstWhere('email', $passwordReset->email);

            // update user password
            $user->update(['password' => bcrypt($request->password)]);


            // delete current code
            $passwordReset->delete();

            return response(['message' => 'password has been successfully reset'], 200);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 500);
        }
    }


public function sendResetLinkEmail(Request $request)
{
    try {
        $data = $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        // Delete all old code that user sent before.
        ResetCodePassword2::where('email', $request->email)->delete();

        // Generate random code
        $data['code'] = mt_rand(100000, 999999);

        // Create a new code
        $codeData = ResetCodePassword2::create($data);

        // Send email to user
        Mail::to($request->email)->send(new SendCodeResetPassword($codeData->code));

        return response(['message' => trans('passwords.sent')], 200);
    } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
    }
}





}
