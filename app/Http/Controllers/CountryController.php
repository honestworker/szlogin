<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

date_default_timezone_set("Europe/Stockholm");

class CountryController extends Controller
{

	public function index(){
		$country = \App\Country::whereNotNull('id');
		
		if(request()->has('idx'))
			$country->where('idx','like','%'.request('idx').'%');
			
        if(request()->has('status'))
            $country->whereStatus(request('status'));
            
        $country->orderBy(request('sortBy'),request('order'));
        
		return $country->paginate(request('pageLength'));
	}

	public function all(){
		$country = \App\Country::whereNotNull('id');
		
        $country->whereStatus(1);
        $country->orderBy('name', 'ASC');
        $countries = $country->pluck('name');
        
		return response()->json(['status' => 'success', 'message' => 'Get Country Data Successfully!', 'countries' => $countries], 200);
	}

    public function store(Request $request){
        
        $validation = Validator::make($request->all(), [
            'idx' => 'required|unique:country',
            'name' => 'required|unique:country',
        ]);
        
        if($validation->fails())
        	return response()->json(['message' => $validation->messages()->first()],422);
        
        $country = new \App\Country;
        $country->fill(request()->all());
        $country->idx = request('idx');
        $country->name = request('name');
        $country->status = 1;
        $country->save();
        
        return response()->json(['message' => 'Country added!', 'data' => $country]);
    }

    public function destroy(Request $request, $id){
        $country = \App\Country::find($id);
        
        if(!$country)
            return response()->json(['message' => 'Couldnot find country!'],422);
            
        $country->delete();
        
        return response()->json(['message' => 'Country deleted!']);
    }

    public function show($idx){
        $country = \App\Country::whereIdx($idx)->first();
        
        if(!$country)
            return response()->json(['message' => 'Couldnot find country!'],422);
            
        return $country;
    }

    public function update(Request $request, $id){
        
        $country = \App\Country::whereId($id)->first();
        
        if(!$country)
            return response()->json(['message' => 'Couldnot find country!']);
            
        $validation = Validator::make($request->all(), [
            'idx' => 'required|unique:country,idx,'.$country->id.',id',
            'name' => 'required',
        ]);
        
        if($validation->fails())
            return response()->json(['message' => $validation->messages()->first()],422);
            
        $country->idx = request('idx');
        $country->name = request('name');
        $country->save();
        return response()->json(['message' => 'Country updated!', 'data' => $country]);
    }

    public function toggleStatus(Request $request){
        $country = \App\Country::find($request->input('id'));
        
        if(!$country)
            return response()->json(['message' => 'Couldnot find country!'],422);
            
        $country->status = !$country->status;
        $country->save();
        
        return response()->json(['message' => 'Country updated!']);
    }
}
