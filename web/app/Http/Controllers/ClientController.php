<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class ClientController extends Controller
{
    public function index ()
    {
        return File::get(public_path() . '/client/index.html');
    }
}
