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
		  'email'=>'required|email',
		  'password'=>'required',
		]);

		if(Auth::attempt(['email'=>$request->email,'password'=>$request->password])){
			
			if(Auth::user()->email=='admin@gmail.com'){
			    return redirect()->route('feedback.all');
			}else{
				return redirect()->route('logout');
			}

		}else{

		  	return redirect()->route('logout');

		}
	}

	public function logout()
  	{
	    Auth::logout();
	    return redirect()->route('login');
  	}

	public function profile()
	{
		$user = User::find(Auth::id());
		return view('admin.profile', compact('user'));
	}

	public function profile_save(Request $request)
    {
        $user = User::find(Auth::id());
        $request->validate([
            'name'=>'required',
            'password'=>'required|confirmed',
        ]);

        if(Auth::attempt([
          'email'=>$user->email,
          'password'=>$request->password_now,
        ])) {
            $user = User::find(Auth::id());
            $user->update([
                'name' => $request->name,
                'password' => bcrypt($request->password)
            ]);
            return redirect()->route('feedback.all');
        } else{
            return redirect()->route('admin.profile');
        }        
    }

}
