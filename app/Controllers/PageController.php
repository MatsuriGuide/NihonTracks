<?php

namespace App\Controllers;

use App\Core\Controller;

class PageController extends Controller
{
    public function about(): void
    {
        $this->render('pages/about', []);
    }

    public function faq(): void
    {
        $this->render('pages/faq', []);
    }
}
