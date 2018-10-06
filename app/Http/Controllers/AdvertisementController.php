<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdvertisementController extends Controller
{
    protected $images_path = 'images/advertisements/';
    protected $image_extensions = array('jpeg', 'png', 'jpg', 'gif', 'svg');
    
	public function index(){
		$advertisement = \App\Advertisement::whereNotNull('id');
		
		// if(request()->has('idx'))
		// 	$advertisement->where('idx','like','%'.request('idx').'%');
			
        // if(request()->has('status'))
        //     $advertisement->whereStatus(request('status'));
            
        // $advertisement->orderBy(request('sortBy'),request('order'));
        
		return $advertisement->paginate(request('pageLength'));
	}

	public function all(){
		$advertisement = \App\Advertisement::whereNotNull('id');
		
        $advertisement->whereStatus(1);
        $advertisement->orderBy('idx', 'ASC');
        $countries = $advertisement->pluck('name');
        
		return response()->json(['status' => 'success', 'message' => 'Get Advertisement Data Successfully!', 'countries' => $countries], 200);
	}

    public function store(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $validation = Validator::make($request->all(), [
            'link' => 'required',
            'country' => 'required',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
        
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array($extension, $this->image_extensions)) {
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg, gif, svg!'], 422);
                }
            }
        }
        
        if ($request->has('id')) {
            $advertisement = \App\Advertisement::find($request->input('id'));
            if (!$advertisement)
                return response()->json(['status' => 'fail', 'message' => 'Could not find the advertisement!'], 422);
        } else {
            $advertisement = new \App\Advertisement;
        }
        $advertisement->link = $request->input('link');
        $advertisement->country = $request->input('country');
        if ($request->has('start_date')) {
            if ($request->input('start_date')) {
                $advertisement->start_date = $request->input('start_date');
            }
        }
        if ($request->has('end_date')) {
            if ($request->input('end_date')) {
                $advertisement->end_date = $request->input('end_date');
            }
        }
        
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                $mt = explode(' ', microtime());
                $name = ((int)$mt[1]) * 1000000 + ((int)round($mt[0] * 1000000));
                $file_name = $name . '.' . $extension;
                
                $file = $image->move($this->images_path, $file_name);
                // $img = \Image::make($this->images_path . $file_name);
                // $img->resize(200, null, function ($constraint) {
                //     $constraint->aspectRatio();
                // });
                // $img->save($this->images_path . $file_name);
                
                $advertisement->image = $file_name;
                break;
            }
        }
        $advertisement->status = 1;
        $advertisement->save();
        
        if ($request->has('id')) {
            return response()->json(['status' => 'success', 'message' => 'Aadvertisement has created succesfully!'], 200);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Aadvertisement has updated succesfully!'], 200);
        }
    }

    public function destroy(Request $request, $id){
        $advertisement = \App\Advertisement::find($id);
        
        if(!$advertisement)
            return response()->json(['message' => 'Couldnot find Advertisement!'],422);
            
        $advertisement->delete();
        
        return response()->json(['message' => 'Advertisement deleted!']);
    }

    public function show($id){
        $advertisement = \App\Advertisement::whereId($id)->first();
        
        if(!$advertisement)
            return response()->json(['message' => 'Couldnot find Advertisement!'],422);
            
        return $advertisement;
    }

    public function update(Request $request, $id){
        
        $advertisement = \App\Advertisement::whereId($id)->first();
        
        if(!$advertisement)
            return response()->json(['message' => 'Couldnot find Advertisement!']);
            
        $validation = Validator::make($request->all(), [
            'idx' => 'required|unique:Advertisement,idx,'.$advertisement->id.',id',
            'name' => 'required',
        ]);
        
        if($validation->fails())
            return response()->json(['message' => $validation->messages()->first()],422);
            
        $advertisement->idx = request('idx');
        $advertisement->name = request('name');
        $advertisement->save();
        return response()->json(['message' => 'Advertisement updated!', 'data' => $advertisement]);
    }

    public function toggleStatus(Request $request){
        $advertisement = \App\Advertisement::find($request->input('id'));
        
        if(!$advertisement)
            return response()->json(['message' => 'Couldnot find Advertisement!'],422);
            
        $advertisement->status = !$advertisement->status;
        $advertisement->save();
        
        return response()->json(['message' => 'Advertisement updated!']);
    }
}
