<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\RegisterViewResponse;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;

use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LogoutResponse;

use App\Http\Requests\LoginRequest;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoginViewResponse::class, function() {
            return new class implements LoginViewResponse {
                public function toResponse($request) {
                    return response()->view('auth.login');
                }
            };
        });

        $this->app->singleton(RegisterViewResponse::class, function() {
            return new class implements RegisterViewResponse {
                public function toResponse($request) {
                    return response()->view('auth.register');
                }
            };
        });

        $this->app->singleton(VerifyEmailViewResponse::class, function() {
            return new class implements VerifyEmailViewResponse {
                public function toResponse($request) {
                    return response()->view('auth.verify-email');
                }
            };
        });

        $this->app->singleton(LoginResponse::class, function() {
            return new class implements LoginResponse {
                public function toResponse($request) {
                    return redirect('/attendance');
                }
            };
        });

        $this->app->singleton(RegisterResponse::class, function() {
            return new class implements RegisterResponse {
                public function toResponse($request) {
                    return redirect('/attendance');
                }
            };
        });

        $this->app->singleton(LogoutResponse::class, function() {
            return new class implements LogoutResponse {
                public function toResponse($request) {
                    return redirect('/login');
                }
            };
        });
    }

    public function boot(): void
    {
        $this->app->bind(
            \Laravel\Fortify\Http\Requests\LoginRequest::class,
            \App\Http\Requests\LoginRequest::class
        );

        $this->app->bind(
            \Laravel\Fortify\Http\Requests\RegisterRequest::class,
            \App\Http\Requests\RegisterRequest::class
        );

        Fortify::authenticateUsing(function (Request $request) {
            if ($request->is('admin/*')) {
                $admin = Admin::where('email', $request->email)->first();

                if ($admin && Hash::check($request->password, $admin->password)) {
                    Auth::guard('admins')->login($admin);
                    return $admin;
                }
            } else {
                $user = User::where('email', $request->email)->first();

                if ($user && Hash::check($request->password, $user->password)) {
                    return $user;
                }
            }
            return null;
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

    }
}
