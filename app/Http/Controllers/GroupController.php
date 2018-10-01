<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class GroupController extends Controller
{

	public function index(){
		$groups = \App\Group::whereNotNull('id');

		if(request()->has('search')) {
            $groups->where('name','like','%'.request('search').'%');
            $groups->where('description','like','%'.request('search').'%');
        }

        if(request()->has('status'))
            $groups->whereStatus(request('status'));

        if (request()->has('sortBy') && request()->has('order') )
            $groups->orderBy(request('sortBy'), request('order'));

		return $groups->paginate(request('pageLength'));
	}

    public function store(Request $request){
        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:groups',
        ]);

        if($validation->fails())
        	return response()->json(['message' => $validation->messages()->first()],422);

        $user = \JWTAuth::parseToken()->authenticate();
        $group = new \App\Group;
        $group->fill(request()->all());
        $group->save();

        return response()->json(['message' => 'Group added!', 'data' => $group]);
    }

    public function toggleStatus(Request $request){
        $group = \App\Group::find($request->input('id'));

        if(!$group)
            return response()->json(['message' => 'Couldnot find group!'],422);

        $group->status = !$group->status;
        $group->save();

        return response()->json(['message' => 'Group updated!']);
    }    

    public function show($id){
        $group = \App\Group::whereId($id)->first();

        if(!$group)
            return response()->json(['message' => 'Couldnot find group!'], 422);

        return $group;
    }

    public function update(Request $request, $id) {

        $group = \App\Group::whereId($id)->first();

        if(!$group)
            return response()->json(['message' => 'Couldnot find group!']);

        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:groups,name,'.$group->id.',id',
            'description' => 'required',
        ]);

        if($validation->fails())
            return response()->json(['message' => $validation->messages()->first()],422);

        $group->name = request('name');
        $group->description = request('description');
        $group->save();

        return response()->json(['message' => 'Group updated!', 'data' => $group]);
    }

}
