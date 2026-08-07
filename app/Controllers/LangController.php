<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Lang;

class LangController extends Controller
{
    public function switch(string $lang): void
    {
        Lang::set($lang);

        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
}
