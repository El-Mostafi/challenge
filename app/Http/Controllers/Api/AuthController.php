<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    /**
     * Afficher le formulaire de connexion
     */
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->isManager()) {
                return redirect('/manager/dashboard');
            }
            return redirect('/employee/dashboard');
        }
        return view('auth.login');
    }

    /**
     * Connexion
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            // Si JSON, retourner erreur JSON
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Les informations de connexion sont invalides.'], 401);
            }
            
            return back()->withErrors([
                'email' => 'Les informations de connexion sont invalides.',
            ])->withInput();
        }

        $request->session()->regenerate();

        // Créer un token pour l'utilisateur
        $user = $request->user();
        $user->tokens()->delete();
        $token = $user->createToken('web-token')->plainTextToken;

        // Si JSON, retourner token
        if ($request->wantsJson()) {
            return response()->json([
                'token' => $token,
                'user' => $user
            ]);
        }

        // Redirection selon le rôle
        if ($user->isManager()) {
            return redirect()->intended('/manager/dashboard');
        }
        
        return redirect()->intended('/employee/dashboard');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        // Supprimer les tokens
        $request->user()->tokens()->delete();
        
        // Si JSON, retourner message JSON
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Déconnecté avec succès']);
        }
        
        // Déconnexion de la session
        Auth::guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
