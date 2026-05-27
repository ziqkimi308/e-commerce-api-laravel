<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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
        $token = $user->createToken('auth_token')->plainTextToken;

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
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        // Manage tokens
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token
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

    // Send email verification notification
    public function sendVerificationEmail(Request $request)
    {
        // If user email already verified
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified'
            ], 400);
        }

        // Verified user email
        // Laravel generates and sent a signed URL that contain {id}, {hash}, expires, and signature
        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Email verification link sent to your email address'
        ]);
    }

    // Actual Verify Email
    public function verify(Request $request)
    {
        // Validate signed URL - the hash part
        // id came from signed URL
        $user = User::findOrFail($request->route('id'));
        // hash_equals same like Hash::check() except it has safety guard against timing-attack
        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success'=>true,
                'message'=>'Invalid verification link'
            ], 400);
        }

        // if user email already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success'=>true,
                'message'=>'Email already verified'
            ]);
        }

        // Verify user email
        if ($user->markEmailAsVerified()) {
            // manually trigger the event
            event(new Verified($user));
        }

        return response()->json([
            'success'=>true,
            'message'=>'Email verified successfully'
        ]);
    }

    // Send password reset link
    public function forgotPassword(Request $request)
    {
        // Validate email format
        $validated = $request->validate([
            'email'=>'required|email'
        ]);

        // Send reset link
        // This link also includes generated unique token for password reset purpose
        $status = Password::sendResetLink($validated['email']);
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to send reset link'
        ], 400);
    }

    // Actual password reset
    public function resetPassword(Request $request)
    {
        // request format validation
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // reset password
        $status = Password::reset(
            //  in the Password::reset() flow, Laravel already passes the new plain password into your callback as $password
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // forceFill is like update, but it bypass the mass-assignment $fillable so we usually use for password reset.
                $user->forceFill([
                    'password'=>$password // We don't need to hash because auto-hashed by User model's casts
                ])->save();

                $user->tokens()->delete();
            }
        );

        // success check
        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to reset password'
        ], 400);
    }
}
