<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check() && Session::has('spai_logged_in')) {
            return redirect()->route('finance.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            // User is authenticated but needs PIN
            Session::put('spai_pre_auth', true);
            return redirect()->route('login.pin');
        }

        return back()->withErrors(['login_error' => 'Kredensial tidak valid.']);
    }

    public function showPinForm()
    {
        if (Auth::check() && Session::has('spai_logged_in')) {
            return redirect()->route('finance.dashboard');
        }
        if (!Auth::check() || !Session::has('spai_pre_auth')) {
            return redirect()->route('login');
        }
        return view('auth.pin');
    }

    public function verifyPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|array|min:6|max:6',
        ]);

        $submittedPin = implode('', $request->pin);
        $user = Auth::user();

        if ($user && $submittedPin === $user->pin) {
            Session::put('spai_logged_in', true);
            Session::forget('spai_pre_auth');
            return redirect()->route('finance.dashboard');
        }

        return back()->withErrors(['pin_error' => 'PIN salah.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::forget('spai_logged_in');
        Session::forget('spai_pre_auth');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
