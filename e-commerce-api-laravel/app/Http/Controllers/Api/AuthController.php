<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
	# use HasApiTokens; is inside model not here!

	public function register(Request $request)
	{
		// Format validation
		$validated = $request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|email|unique:users,email', # or Rule::unique('user', 'email')
			'password' => 'required|string|min:6|confirmed'
		]);

		// Create query
		$user = User::create($validated);

		// Trigger the event
		// Register is built-in event
		event(new Registered($user));

		// Generate token
		$token = $user->createToken('auth_token')->plaintTextToken;

		return response()->json([
			'success' => true,
			'message' => 'Registration successful. Please verify your email.',
			'user' => new UserResource($user),
			'token' => $token
		], 201);
	}

	public function login(Request $request)
	{
		// Validate format
		$validated = $request->validate([
			'email' => 'required|email',
			'password' => 'required'
		]);

		// Validate password
		$user = User::where('email', $validated['email'])->first();
		if (!$user || Hash::check($validated['password'], $user->password)) {
			throw ValidationException::withMessages([
				'email'=>['The provided credentials are incorrect.']
			]);
		}

		// Manage tokens
		$user->tokens()->delete();
		$token = $user->createToken('auth_token')->plainTextToken;

		return response()->json([
			'success'=>true,
			'message'=>'Login successful',
			'user'=>new UserResource($user),
			'token'=>$token
		]);
	}

	public function logout(Request $request)
	{
		// Manage token
		$request->user()->currentAccessToken()->delete();

		return response()->json([
			'success' => true,
			'message' => 'Logged out successfully'
		]);
	}

	public function me(Request $request)
	{
		return response()->json([
			'success' => true,
			'user' => new UserResource($request->user())
		]);
	}
}
