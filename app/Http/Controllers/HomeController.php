<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    function index()
    {
        $pageTitle = 'Home';
        return view('home', ['pageTitle' => $pageTitle]);
    }
}
