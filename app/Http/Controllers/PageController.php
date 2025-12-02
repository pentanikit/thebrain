<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    // Client Router
    public function home(Request $request){
        return view('frontend.pages.home');
    }




    //Admin Router
    public function dashboard(){
        return view('backend.dashboard');
    }
    

}
