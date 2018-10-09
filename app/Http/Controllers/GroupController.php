<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class GroupController extends Controller
{

	public function index(){
		$groups = \App\Group::whereNotNull('id');
		
		if(request()->has('group_id')) {
            $groups->where('group_id','like','%'.request('group_id').'%');
        }
        
		if(request()->has('org_number')) {
            $groups->where('group_id','like','%'.request('org_number').'%');
        }
        
		if(request()->has('contact_person')) {
            $groups->where('group_id','like','%'.request('contact_person').'%');
        }
        
		if(request()->has('org_name')) {
            $groups->where('group_id','like','%'.request('org_name').'%');
        }
        
		if(request()->has('email')) {
            $groups->where('group_id','like','%'.request('email').'%');
        }
        
		if(request()->has('mobile_number')) {
            $groups->where('group_id','like','%'.request('mobile_number').'%');
        }
        
		if(request()->has('country')) {
            $groups->where('group_id','like','%'.request('country').'%');
        }
        
        if(request()->has('status'))
            $groups->whereStatus(request('status'));
            
        if (request()->has('sortBy') && request()->has('order') )
            $groups->orderBy(request('sortBy'), request('order'));
            
		return $groups->paginate(request('pageLength'));
	}

	public function all(){
		$groups = \App\Group::whereNotNull('id');
		
        $groups->whereStatus(1);
        $groups->orderBy('group_id', 'ASC');
        
		return response()->json(['status' => 'success', 'message' => 'Get Group Data Successfully!', 'data' => $groups->get()], 200);
	}
	
    public function store(Request $request){
        $validation = Validator::make($request->all(), [
            'group_id' => 'required|unique:groups',
            'org_number' => 'required',
            'contact_person' => 'required',
            'org_name' => 'required',
            'email' => 'required|email|unique:users',
            'mobile_number' => 'required',
            'country' => 'required',
        ]);
        
        if($validation->fails())
        	return response()->json(['status' => 'success', 'message' => $validation->messages()->first()],422);

        $user = \JWTAuth::parseToken()->authenticate();
        $group = new \App\Group;
        $group->fill(request()->all());
        $group->status = 1;
        $group->save();

        return response()->json(['status' => 'success', 'message' => 'Group added!', 'data' => $group]);
    }

    public function toggleStatus(Request $request){
        $group = \App\Group::find($request->input('id'));
        
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find group!'],422);
            
        $group->status = !$group->status;
        $group->save();
        
        return response()->json(['status' => 'success', 'message' => 'Group updated!']);
    }    

    public function show($id){
        $group = \App\Group::whereId($id)->first();
        
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find group!'], 422);
            
        return $group;
    }

    public function update(Request $request, $id) {
        
        $group = \App\Group::whereId($id)->first();
        
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find group!']);
            
        $validation = Validator::make($request->all(), [
            'group_id' => 'required|unique:groups,group_id,'.$group->id.',id',
            'org_number' => 'required',
            'contact_person' => 'required',
            'org_name' => 'required',
            'email' => 'required|email|unique:users',
            'mobile_number' => 'required',
            'country' => 'required',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'success', 'message' => $validation->messages()->first()],422);
            
        $group->group_id = request('group_id');
        $group->org_number = request('org_number');
        $group->contact_person = request('contact_person');
        $group->org_name = request('org_name');
        $group->email = request('email');
        $group->mobile_number = request('mobile_number');
        $group->country = request('country');
        $group->save();
        
        return response()->json(['message' => 'Group updated!', 'data' => $group]);
    }

    public function overview(Request $request) {
        $total = \App\Group::count();
        
        $infor = array();
        
        $now_date = date("Y-m-d");
        $year = date('Y', strtotime($now_date));
        $month = date('m', strtotime($now_date));
        for ($month_index = 1; $month_index <= $month; $month_index++) {
            $groups = \App\Group::whereNotNull('id');
            $infor[] = count($groups->whereYear('created_at', '=',  $year)->whereMonth('created_at', '=',   $month_index )->get());
        }
        
        return response()->json(['status' => 'success', 'message' => 'Group overview!', 'data' => compact('total', 'infor', 'year')]);
    }
}
