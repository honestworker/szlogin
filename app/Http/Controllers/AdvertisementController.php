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
		$advertisements = \App\Advertisement::whereNotNull('id');
		
		if(request()->has('country'))
		    $advertisements->where('country', request('country'));
			
        if(request()->has('status'))
            $advertisements->whereStatus(request('status'));

        if(request()->has('show_count_oper') && request()->has('show_count'))
            $advertisements->where('show_count', request('show_count_oper'), request('show_count'));

        if(request()->has('link_count_oper') && request()->has('link_count'))
            $advertisements->where('link_count', request('link_count_oper'), request('link_count'));

        if(request()->has('start_date_oper') && request()->has('start_date')) {
            $advertisements->where('start_date', '!=', '')
                ->where('start_date', request('start_date_oper'), request('start_date'));
        }
            
        if(request()->has('end_date_oper') && request()->has('end_date')) {
            $advertisements->where('end_date', '!=', '')
                ->where('end_date', request('end_date_oper'), request('end_date'));
        }
        
        $advertisements->select('id', 'country', 'image', 'link', 'name', 'start_date', 'end_date', 'status', \DB::raw('(select count(*) from ads_count where ads_count.ad_id = ads.id and ads_count.type = \'show\') as show_count'), \DB::raw('(select count(*) from ads_count where ads_count.ad_id = ads.id and ads_count.type = \'click\') as click_count'));

        //$advertisements->orderBy(request('sortBy'), request('order'));
        
		return $advertisements->paginate(request('pageLength'));
	}

    public function store(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $validation = Validator::make($request->all(), [
            'name' => 'required|min:1',
            'link' => 'required|min:1',
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
        
        if($request->has('id')) {
            $advertisement = \App\Advertisement::whereId(request('id'))->first();
            if (!$advertisement) {
                return response()->json(['status' => 'fail', 'message' => 'Could not find the advertisement!'], 422);
            }
        } else {
            $advertisement = \App\Advertisement::where('name', '=', request('name'))->first();
            if ($advertisement) {
                return response()->json(['status' => 'fail', 'message' => 'You must specify the unique name!'], 422);
            }
            $advertisement = new \App\Advertisement;
        }
        $advertisement->name = request('name');
        $advertisement->link = request('link');
        $advertisement->country = request('country');
        if ($request->has('start_date')) {
            if (request('start_date')) {
                $advertisement->start_date = request('start_date');
            }
        }
        if ($request->has('end_date')) {
            if (request('end_date')) {
                $advertisement->end_date = request('end_date');
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
                
                $advertisement->image = $file_name;
                break;
            }
        }
        $advertisement->status = 1;
        $advertisement->save();
        
        return response()->json(['status' => 'success', 'message' => 'Aadvertisement has created succesfully!'], 200);
    }

    public function destroy(Request $request, $id){
        $advertisement = \App\Advertisement::find($id);
        
        if(!$advertisement)
            return response()->json(['message' => 'Couldnot find Advertisement!'], 422);
            
        $advertisement->delete();
        
        return response()->json(['message' => 'Advertisement deleted!']);
    }

    public function show($id){
        $advertisement = \App\Advertisement::whereId($id)->first();
        
        if(!$advertisement)
            return response()->json(['message' => 'Couldnot find Advertisement!'], 422);
            
        return $advertisement;
    }

    public function update(Request $request, $id) {
        $advertisement = \App\Advertisement::whereId($id)->first();
        
        if(!$advertisement)
            return response()->json(['status' => 'fail', 'message' => 'Could not find the advertisement!'], 422);
        
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'link' => 'required',
            'country' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['message' => $validation->messages()->first()], 422);
            
        $advertisement->name = request('name');
        $advertisement->link = request('link');
        $advertisement->country = request('country');
        if ($request->has('start_date')) {
            if (request('start_date')) {
                $advertisement->start_date = request('start_date');
            }
        }
        if ($request->has('end_date')) {
            if (request('end_date')) {
                $advertisement->end_date = request('end_date');
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
                
                $advertisement->image = $file_name;
                break;
            }
        }
        $advertisement->status = 1;
        $advertisement->save();
        
        return response()->json(['message' => 'Advertisement updated!', 'data' => $advertisement]);
    }

    public function toggleStatus(Request $request){
        $advertisement = \App\Advertisement::find(request('id'));
        
        if(!$advertisement)
            return response()->json(['message' => 'Couldnot find Advertisement!'], 422);
            
        $advertisement->status = !$advertisement->status;
        $advertisement->save();
        
        return response()->json(['message' => 'Advertisement updated!']);
    }

    public function get(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $advertisements = \App\Advertisement::whereNotNull('id');
        
        $now_date = date("Y-m-d m:i:s");
        $advertisements->where('start_date', '!=', '')->where('start_date', '<=', $now_date)->where('end_date', '!=', '')->where('end_date', '>=', $now_date)->whereStatus(1);
        if ($profile->country) {
            $advertisements->where('country', '=', $profile->country);
        }
        
        $advertisements_result = $advertisements->get();
        $advertisements_count = count($advertisements_result);
        
        if (!$advertisements_count) {
            return response()->json(['status' => 'fail', 'message' => 'Advertisement fail!'], 422);
        }
        $advertisement_index = rand(1, $advertisements_count);
        $advertisement = $advertisements_result[$advertisement_index - 1];
        
        $visitor = \App\AdvertisementCount::create([
            'ad_id' => $advertisement->id,
            'type' => 'show',
        ]);
        
        return response()->json(['status' => 'success', 'message' => 'Advertisement!', 'id' => $advertisement->id, 'image' => $advertisement->image, 'link' => $advertisement->link], 200);
    }

    public function click(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false], 422);
        }
        
        $validation = Validator::make($request->all(), [
            'advertisement_id' => 'required'
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $advertisement = \App\Advertisement::find(request('advertisement_id'));
        if(!$advertisement)
            return response()->json(['status' => 'fail', 'message' => 'Could not find the advertisement!'], 422);
            
        $visitor = \App\AdvertisementCount::create([
            'ad_id' => $advertisement->id,
            'type' => 'click',
        ]);
        
        return response()->json(['status' => 'success', 'message' => 'Advertisement Clicked!'], 200);
    }

    public function overview(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false], 422);
        }
        
        $now_date = date("Y-m-d");
        $year = date('Y', strtotime($now_date));
        $month = date('m', strtotime($now_date));
        $month_before = date("Y-m-d H:i:s", strtotime("$now_date  -30 days"));
        
        $show_count_infor = $click_count_infor = [];
        for ($month_index = 1; $month_index <= $month; $month_index++) {
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $show_count_infor[] = count($advertisement_counts->whereYear('created_at', '=',  $year)->whereMonth('created_at', '=',   $month_index )->where('type', '=',  'show')->get());
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $click_count_infor[] = count($advertisement_counts->whereYear('created_at', '=',  $year)->whereMonth('created_at', '=',   $month_index )->where('type', '=',  'click')->get());
        }
        
        $statistics = [];
        $advertisements = \App\Advertisement::whereNotNull('id')->get();
        foreach ($advertisements as $advertisement) {
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $show_all = count($advertisement_counts->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->get());
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $show_month = count($advertisement_counts->where('created_at', '>=',  $month_before)->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->get());
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $click_all = count($advertisement_counts->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->get());
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $click_month = count($advertisement_counts->where('created_at', '>=',  $month_before)->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->get());
            $statistics[] = [$advertisement->name, $show_all, $show_month, $click_all, $click_month];
        }
        return response()->json(['status' => 'success', 'message' => 'Advertisement Overview!', 'data' => compact('show_count_infor', 'click_count_infor', 'statistics')], 200);
    }
}
