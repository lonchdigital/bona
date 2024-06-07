<?php

namespace App\Services\Auth;

use App\Exceptions\ApplicationDomainException;
use App\Mail\UserEmailConfirmationEmail;
use App\Mail\UserForgotPasswordEmail;
use App\Models\Role;
use App\Models\User;
use App\Models\UserEmailActivationCode;
use App\Services\Auth\DTO\ConfirmEmailDTO;
use App\Services\Auth\DTO\ConfirmEmailResendDTO;
use App\Services\Auth\DTO\ForgotPasswordDTO;
use App\Services\Auth\DTO\SignInDTO;
use App\Services\Auth\DTO\SignUpDTO;
use App\Services\Base\BaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\Concerns\Has;

class AuthService extends BaseService
{
    public function signUp(SignUpDTO $request): void
    {
        try {
            DB::beginTransaction();

            $userLanguage = app()->getLocale();

            //create new user
            $user = User::create([
                'email' => $request->email,
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'phone' => $request->phone,
                'role_id' => Role::USER_ROLE_ID,
                'language' => $userLanguage,
                'password' => Hash::make($request->password),
            ]);

            $userEmailActivationCode = UserEmailActivationCode::create([
                'user_id' => $user->id,
                'code' => Str::random(64),
            ]);


            Mail::to($user->email)->send(new UserEmailConfirmationEmail($userEmailActivationCode->code));

            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollback();
            $this->logCaughtException($throwable);
            throw $throwable;
        }


    }

    /**
     * returns true if the code is valid or false if the code is not valid or empty
     */
    public function confirmEmail(ConfirmEmailDTO $request): bool
    {
        $code = UserEmailActivationCode::where('code', $request->code)->first();

        if (!$code) {
            return false;
        }

        try {
            DB::beginTransaction();

            $code->user->update([
                'email_verified_at' => Carbon::now(),
            ]);

            $code->delete();

            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollback();
            $this->logCaughtException($throwable);
            throw $throwable;
        }

        return true;
    }

    public function confirmEmailResend(ConfirmEmailResendDTO $request): void
    {
        $user = User::where('email', $request->email)->first();

        $userEmailActivationCode = UserEmailActivationCode::create([
            'user_id' => $user->id,
            'code' => Str::random(64),
        ]);

        Mail::to($user->email)->send(new UserEmailConfirmationEmail($userEmailActivationCode->code));
    }

    public function signIn(SignInDTO $request): bool
    {
        $authAttempt = Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ], $request->rememberMe);

        if ($authAttempt) {
            request()->session()->regenerate();
        }

        return $authAttempt;
    }

    public function logout(): void
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();
    }

    public function resetPassword(ForgotPasswordDTO $request): void
    {
        $newPassword = Str::random(8);

        $user = User::where('email', $request->email)->first();

        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        Mail::to($request->email)->send(new UserForgotPasswordEmail($newPassword));
    }
}
