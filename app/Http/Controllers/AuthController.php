<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function show()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $expected = (string) config('admin.password');
        if ($expected !== '' && hash_equals($expected, (string) $request->input('password'))) {
            $request->session()->regenerate();
            $request->session()->put('is_admin', true);
            return redirect('/');
        }

        return back()->withErrors(['password' => 'Wrong password.']);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}