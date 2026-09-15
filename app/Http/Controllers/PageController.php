<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Affiche la page d'accueil.
     */
    public function home(): View
    {
        return view('welcome');
    }

    /**
     * Affiche la page "À propos".
     */
    public function about(): View
    {
        return view('pages.about');
    }

    /**
     * Affiche la page de contact.
     */
    public function contact(): View
    {
        return view('pages.contact');
    }

    /**
     * Affiche la page des tarifs.
     */
    public function pricing(): View
    {
        return view('pages.pricing');
    }
}
