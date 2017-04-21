<?php

namespace App\Http\Controllers;

use iHubGrid\ErrorHandler\Http\Controllers\Controller;

class FrontController extends Controller
{
    public function index()
    {
        return view('welcome');
    }
}
