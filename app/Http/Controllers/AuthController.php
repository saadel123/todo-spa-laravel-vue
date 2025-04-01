<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the incoming request to ensure email and password are provided
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Retrieve the user based on the provided email
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // If credentials are incorrect, throw a validation exception with an error message
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // If authentication is successful, generate and return an API token for the user
        return response()->json([
            'token' => $user->createToken('authToken')->plainTextToken
        ]);
    }

    public function logout(Request $request)
    {
        // Delete all tokens associated with the authenticated user
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}

