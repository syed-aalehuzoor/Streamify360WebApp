<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Http;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->after(function ($validator) use ($input) {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret'),
                'response' => $input['g-recaptcha-response'],
                'remoteip' => request()->ip(),
            ]);
            if($response->json()['success'] == false) {
                $validator->errors()->add('captcha', 'Captcha verification failed.');
            }
        })
        /*->after(function ($validator) use ($input) {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret'),
                'response' => $input['g-recaptcha-response'],
                'remoteip' => request()->ip(),
            ]);
            if($response->json()['success'] == false) {
                return response()->json(['error' => 'Captcha verification failed.'], 422);
            }
            $response = Http::withToken(config('services.sendgrid.key'))
                ->get('https://api.sendgrid.com/v3/validations/email', [
                    'email' => $input['email'],
                ]);
            if (!$response->successful()) {
                $validator->errors()->add('email', 'Email must be a valid address.');
            }
        })*/
        ->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
