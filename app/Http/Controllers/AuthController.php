<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function google_callback(){
    
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Failed to authenticate with Google.');
        }
        
        $user = User::updateOrCreate([
            'email' => $googleUser->getEmail(),
        ], [
            'name' => $googleUser->getName(),
            'google_id' => $googleUser->getId(),
            'password' => bcrypt(Str::random(24)),
        ]);
        if ($googleUser->getAvatar()) {
            $this->storeProfilePhoto($user, $googleUser->getAvatar());
        }
        Auth::login($user, true);
    
        return redirect()->intended('/dashboard');
    }

    protected function storeProfilePhoto($user, $avatarUrl)
    {
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }
        $avatarContents = file_get_contents($avatarUrl);
        $avatarPath = 'profile-photos/' . Str::random(40) . '.jpg';
        Storage::disk('public')->put($avatarPath, $avatarContents);
        $user->forceFill([
            'profile_photo_path' => $avatarPath,
        ])->save();
    }
}
