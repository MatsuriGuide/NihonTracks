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
        $lang = in_array(\App\Core\Lang::current(), ['fr', 'en', 'ja'], true)
            ? \App\Core\Lang::current()
            : 'fr';

        $this->render('pages/faq_' . $lang, []);
    }
}
