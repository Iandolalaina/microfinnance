<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Affiche le formulaire de connexion (requête GET /login).
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Traite la soumission du formulaire (requête POST /login).
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. On valide que les champs sont bien remplis
        $credentials = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        // 2. Auth::attempt() vérifie automatiquement le mot de passe hashé
        //    en base (grâce au cast 'password' => 'hashed' dans le modèle User)
        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            // 3. Sécurité : on régénère l'identifiant de session après connexion
            $request->session()->regenerate();

            return $this->redirectByRole();
        }

        // 4. Si échec : on revient au formulaire avec un message d'erreur
        return back()
            ->withErrors(['phone' => 'Numéro de téléphone ou mot de passe incorrect.'])
            ->onlyInput('phone');
    }

    /**
     * Redirige l'utilisateur connecté vers SON tableau de bord,
     * selon le rôle stocké dans la colonne "role" de la table users.
     */
    protected function redirectByRole(): RedirectResponse
    {
        return match (Auth::user()->role) {
            'admin' => redirect()->intended('/admin/dashboard'),
            'agent' => redirect()->intended('/agent/dashboard'),
            default => redirect()->intended('/client/dashboard'), // 'client'
        };
    }

    /**
     * Déconnexion (requête POST /logout).
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
