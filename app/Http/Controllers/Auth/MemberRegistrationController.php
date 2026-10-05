<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberRegistrationController extends Controller
{
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'cin' => ['required', 'digits:12', 'unique:users,cin'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'region' => ['required', 'string', 'max:150'],
            'fokontany' => ['required', 'string', 'max:150'],
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'matricule.unique' => 'Ce matricule est déjà utilisé.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'cin.digits' => 'Le numéro CIN doit contenir exactement 12 chiffres.',
            'cin.unique' => 'Ce numéro CIN est déjà associé à un compte.',
            'email.unique' => 'Cette adresse e-mail est déjà associée à un compte.',
            'profile_photo.required' => 'La photo de profil est obligatoire.',
            'profile_photo.image' => 'Le fichier doit être une image.',
            'profile_photo.mimes' => 'La photo doit être au format JPG, PNG ou WEBP.',
            'profile_photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $validated['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
        $validated['role'] = 'client';
        $validated['is_active'] = true;
        unset($validated['password_confirmation']);

        $member = DB::transaction(function () use ($validated): User {
            $member = User::create($validated);
            $sequence = $member->id;

            do {
                $matricule = 'MTS-'.now()->format('Y').'-'.str_pad((string) $sequence++, 6, '0', STR_PAD_LEFT);
            } while (User::where('matricule', $matricule)->exists());

            $member->update(['matricule' => $matricule]);

            return $member;
        });

        Auth::login($member);
        $request->session()->regenerate();

        return redirect('/client/dashboard')->with('member_matricule', $member->matricule);
    }
}