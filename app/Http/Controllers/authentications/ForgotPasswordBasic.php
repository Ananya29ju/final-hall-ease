<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Auth\Events\PasswordReset;

class ForgotPasswordBasic extends Controller
{
    /**
     * Get the dashboard route based on authenticated user's role
     */
    private function getDashboardRoute(): string
    {
        return match (true) {
            Auth::user()->isAdmin() => 'admin.dashboard',
            Auth::user()->isMedia() => 'media.dashboard',
            default => 'user.dashboard',
        };
    }

    public function index()
    {
        if (Auth::check()) {
            return redirect()->route($this->getDashboardRoute());
        }

        return view('auth.forgot');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (strtolower($value) !== 'admin@example.com' && !str_ends_with(strtolower($value), '@staloysius.edu.in')) {
                        $fail('Only institutional email addresses ending with @staloysius.edu.in are allowed.');
                    }
                },
            ],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, string $token)
    {
        if (Auth::check()) {
            return redirect()->route($this->getDashboardRoute());
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (strtolower($value) !== 'admin@example.com' && !str_ends_with(strtolower($value), '@staloysius.edu.in')) {
                        $fail('Only institutional email addresses ending with @staloysius.edu.in are allowed.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', PasswordRule::min(6)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}
