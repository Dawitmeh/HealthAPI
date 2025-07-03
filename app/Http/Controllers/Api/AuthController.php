<?php

namespace App\Http\Controllers\Api;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|max:15|unique:users',
                'password' => 'required|string|confirmed',
            ], [
                'email.unique' => 'The email has already been taken',
                'phone.unique' => 'The phone is already been taken',
                'password.confirmed' => 'The password confirmation does not match'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $rawPhone = ltrim($request->phone, '0');
            $phone = '+251' . $rawPhone;

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $phone,
                'phone_verified_at' => now(),
                'last_login_at' => now(),
                'password' => Hash::make($request->password)
            ]);

            $token = $user->createToken('main')->plainTextToken;

            return response()->json([
                'Message' => 'Registration successful',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string'
            ], [
                'email.required' => 'Email is required',
                'password.required' => 'Password is required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            // Attempt to find the user
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid Credentials'
                ], 401);
            }

            // Generate a new token
            $token = $user->createToken('authToken')->plainTextToken;

            $user->update([
                'last_login_at' => now()
            ]);

            return response()->json([
                'message' => 'Login successful', 
                'user' => $user,
                'token' => $token
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }

    public function adminLogin(Request $request) 
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string'
            ], [
                'email.required' => 'Email is required',
                'password.required' => 'password is required'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid Credentials'
                ], 401);
            }

            $token = $user->createToken('authToken')->plainTextToken;

            $user->update([
                'last_login_at' => now()
            ]);

            return response()->json([
                'message' => 'Admin login successful',
                'user' => $user,
                'token' => $token
            ], 200);

        } catch (Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage()
            ], 500);
        }
    }
}
