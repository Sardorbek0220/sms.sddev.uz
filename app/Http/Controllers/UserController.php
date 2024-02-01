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
			}else if(Auth::user()->email=='operator@gmail.com'){
			    return redirect()->route('monitoring');
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

	public function profile($id)
	{
		$user = User::find($id);
		return view('admin.profile', compact('user'));
	}

	public function profile_save(Request $request)
    {
        $user = User::find($request->id);
        $request->validate([
            'name'=>'required',
            'password'=>'required|confirmed',
        ]);

        if($user) {
            $user->update([
                'name' => $request->name,
                'password' => bcrypt($request->password)
            ]);
            return redirect()->route('feedback.all');
        } else{
            return redirect()->route('admin.profile');
        }        
    }

	public function monitoring()
	{
		return view('monitoring');
	}

	public function redirect()
	{
		if (empty(Auth::user())) {
			return redirect()->route('logout');
		}else if(Auth::user()->email=='operator@gmail.com'){
			return redirect()->route('monitoring');
		}else if(Auth::user()->email=='admin@gmail.com'){
			return redirect()->route('feedback.all');
		}
	}

}
