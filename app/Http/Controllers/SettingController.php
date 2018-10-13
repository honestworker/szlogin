<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

date_default_timezone_set("Europe/Stockholm");

class SettingController extends Controller
{
	public function store(Request $request){

        $input = $request->all();
        foreach($input as $key => $value){
            $value = (is_array($value)) ? implode(',', $value) : $value;
            $setting = \App\Setting::firstOrNew(['name' => $key]);
            $setting->value = $value;
            $setting->save();
        }

		$config = \App\Setting::all()->pluck('value','name')->all();

        return response()->json(['message' => 'Setting stored successfully!']);
	}

	public function index(){
		$setting = \App\Setting::all()->pluck('value','name')->all();
		return response()->json(compact('setting'));
	}
}
