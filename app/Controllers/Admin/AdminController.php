<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;

/**
 * Base commune à tous les contrôleurs du back-office.
 * Par défaut, accessible aux modérateurs et admins ; les contrôleurs
 * réservés aux admins (tags, utilisateurs, traductions) surchargent
 * le constructeur avec Auth::requireRole('admin').
 */
abstract class AdminController extends Controller
{
    public function __construct()
    {
        Auth::requireRole(['moderator', 'admin']);
    }
}
