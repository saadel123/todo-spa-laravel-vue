<?php
declare(strict_types=1);// Enforce strict types in this file

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Handle user login and return a token.
     */
    public function login(Request $request): JsonResponse
    {
        // Validate the incoming request to ensure email and password are provided
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Retrieve the user based on the provided email
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            // If credentials are incorrect, throw a validation exception with an error message
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // If authentication is successful, generate and return an API token for the user
        return response()->json([
            'token' => $user->createToken('authToken')->plainTextToken
        ], 200);
    }

    /**
     * Handle user logout and delete all tokens.
     */
    public function logout(Request $request): JsonResponse
    {
        // Ensure the user is authenticated before attempting to delete tokens
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Logged out'], 200);
    }
}
