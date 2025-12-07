<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    // Client Router
    public function home(Request $request){
        return view('frontend.pages.home');
    }


    public function banners(){
        return view('backend.banners.banner-upload');
    }

    //Admin Router
    public function dashboard(){
        return view('backend.dashboard');
    }

    public function category(){
        return view('frontend.pages.category');
    }
    

}
