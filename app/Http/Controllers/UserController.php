<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{

    public function show(){
        return view('backend.login');
    }

    public function login(Request $request)
    {
        // Validate login form
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Credentials array
        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
        ];

        // Attempt login
        if (Auth::attempt($credentials, $request->remember)) {
            
            // Regenerate session for security
            $request->session()->regenerate();

            // Redirect to dashboard or intended route
            return redirect()->route('admindashboard')
                ->with('success', 'Login successful!');
        }

        // Login failed
        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->withInput();
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('loginform')->with('success', 'Logged out successfully.');
    }

}
