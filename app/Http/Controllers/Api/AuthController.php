<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\Auditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    public function login(LoginRequest $request, Auditor $auditor)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $auditor->log('auth.login_failed', 'user', $user?->id, null, ['email' => $request->email]);
            return $this->errorResponse('Invalid credentials', 401);
        }

        if (!$user->active) {
            $auditor->log('auth.login_disabled', 'user', $user->id);
            return $this->errorResponse('Account is disabled', 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Set the auth context manually so the audit row carries the user_id.
        auth()->setUser($user);
        $auditor->log('auth.login', 'user', $user->id);

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name,
                'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
            ],
            'token' => $token,
        ], 'Logged in successfully');
    }

    public function user(Request $request)
    {
        $user = $request->user();
        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first()?->name,
            'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
        ], 'User fetched successfully');
    }

    public function logout(Request $request, Auditor $auditor)
    {
        $userId = $request->user()->id;
        $request->user()->currentAccessToken()->delete();
        $auditor->log('auth.logout', 'user', $userId);

        return $this->successResponse(null, 'Logged out successfully');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $status = Password::broker()->sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return $this->successResponse(null, __($status));
        }

        return $this->errorResponse(__($status));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(null, __($status));
        }

        return $this->errorResponse(__($status));
    }
}
