<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperatorSettingsController extends Controller
{
    public function index()
    {
        return view('operator.settings', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $user->live_survey_widget_enabled = $request->boolean('live_survey_widget_enabled');
        $user->save();

        return redirect()->route('operator.settings')->with('success', 'Настройки сохранены');
    }
}
