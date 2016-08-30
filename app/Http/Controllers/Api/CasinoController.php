<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Response;

class CasinoController extends Controller
{
    public function index(){
        return response()->json(['name' => 'Abigail', 'state' => 'CA']);
    }
}
