<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class UserController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function login_store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return back()->withErrors([
                'email' => 'Логин или пароль неверный.',
            ])->withInput($request->only('email'));
        }

        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('feedback.all');
        }

        if ($user->isOperator() && !empty($user->operator_id)) {
            return redirect()->route('operator.workspace');
        }

        Auth::logout();

        return back()->withErrors([
            'email' => 'Для этого пользователя не настроен доступ к оператору.',
        ])->withInput($request->only('email'));
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('login');
    }

    public function profile($id)
    {
        $user = User::find($id);

        return view('admin.profile', compact('user'));
    }

    public function profile_save(Request $request)
    {
        $user = User::find($request->id);
        $request->validate([
            'name' => 'required',
            'password' => 'required|confirmed',
        ]);

        if ($user) {
            $user->update([
                'name' => $request->name,
                'password' => bcrypt($request->password),
            ]);

            return redirect()->route($user->isAdmin() ? 'feedback.all' : 'operator.workspace');
        }

        return redirect()->route('admin.profile');
    }

    public function monitoring()
    {
        return view('monitoring');
    }

    public function redirect()
    {
        if (empty(Auth::user())) {
            return redirect()->route('logout');
        } elseif (Auth::user()->isOperator() && !empty(Auth::user()->operator_id)) {
            return redirect()->route('operator.workspace');
        } elseif (Auth::user()->isAdmin()) {
            return redirect()->route('feedback.all');
        }

        return redirect()->route('logout');
    }
}
