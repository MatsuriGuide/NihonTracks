<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function registerForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $this->render('auth/register', ['errors' => [], 'old' => []]);
    }

    public function register(): void
    {
        $email            = trim((string) $this->input('email', ''));
        $password         = (string) $this->input('password', '');
        $passwordConfirm  = (string) $this->input('password_confirm', '');
        $displayName      = trim((string) $this->input('display_name', ''));

        $errors = [];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse email invalide.';
        } elseif (User::emailExists($email)) {
            $errors[] = 'Un compte existe déjà avec cet email.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif ($password !== $passwordConfirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if ($displayName === '') {
            $errors[] = "Le nom d'affichage est requis.";
        }

        if (!empty($errors)) {
            $this->render('auth/register', [
                'errors' => $errors,
                'old'    => ['email' => $email, 'display_name' => $displayName],
            ]);

            return;
        }

        $userId = User::create($email, $password, $displayName);
        $user = User::findById($userId);

        Auth::login($user);
        $this->redirect('/');
    }

    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }

        $this->render('auth/login', ['errors' => [], 'old' => []]);
    }

    public function login(): void
    {
        $email    = trim((string) $this->input('email', ''));
        $password = (string) $this->input('password', '');

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->render('auth/login', [
                'errors' => ['Email ou mot de passe incorrect.'],
                'old'    => ['email' => $email],
            ]);

            return;
        }

        if (!(bool) $user['is_active']) {
            $this->render('auth/login', [
                'errors' => ['Ce compte a été désactivé.'],
                'old'    => ['email' => $email],
            ]);

            return;
        }

        Auth::login($user);
        $this->redirect('/');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/');
    }
}
