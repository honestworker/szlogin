<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

date_default_timezone_set("Europe/Stockholm");

class AdvertisementController extends Controller
{
    protected $images_path = 'images/advertisements/';
    protected $images_base_path = 'images/advertisements/base.png';
    protected $image_extensions = array('jpeg', 'png', 'jpg');

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
        
        $advertisements->select('id', 'country', 'image', 'link', 'name', 'start_date', 'end_date', 'status', \DB::raw('(select count(*) from ads_count where ads_count.ad_id = ads.id and ads_count.type = \'show\') as show_count'), \DB::raw('(select sum(count) from ads_count where ads_count.ad_id = ads.id and ads_count.type = \'show\') as show_sum'), \DB::raw('(select count(*) from ads_count where ads_count.ad_id = ads.id and ads_count.type = \'click\') as click_count'), \DB::raw('(select sum(count) from ads_count where ads_count.ad_id = ads.id and ads_count.type = \'click\') as click_sum'));

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
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg!'], 422);
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
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $advertisements = \App\Advertisement::whereNotNull('id');
        
        $now_date = date("Y-m-d");
        $advertisements->whereRaw("((`start_date` IS NOT NULL AND `end_date` IS NOT NULL AND `start_date` <= '" . $now_date . "' AND `end_date` >= '" . $now_date . "') OR (`start_date` IS NULL AND `end_date` IS NULL) OR (`start_date` IS NULL AND `end_date` >= '" . $now_date . "') OR (`end_date` IS NULL AND `start_date` <= '" . $now_date . "'))");
        $advertisements->whereStatus(1);
        if ($profile->country) {
            $advertisements->where('country', '=', $profile->country);
        }

        $advertisements_result = $advertisements->get();
        if ($advertisements_result) {
            $advertisements_count = count($advertisements_result);
        }
        if (!$advertisements_count) {
            return response()->json(['status' => 'success', 'message' => 'Advertisement!', 'id' => 0, 'image' => basename($this->images_base_path), 'link' => url('/')], 200);
        }
        
        $min_count = \App\AdvertisementCount::whereUserId($user->id)->whereViewDate($now_date)->whereType('show')->orderBy('count', 'asc')->first();
        if (!$min_count) {
            $advertisement_index = rand(1, $advertisements_count);
            $advertisement = $advertisements_result[$advertisement_index - 1];
            
            $advertisement_count = \App\AdvertisementCount::create([
                'ad_id' => $advertisement->id,
                'type' => 'show',
                'user_id' => $user->id,
                'view_date' => $now_date,
                'count ' => 1,
            ]);
        } else {
            $new_advertisements = array();
            $new_advertisements_exist = array();
            $all_show = 1;
            foreach ($advertisements_result as $advertisement) {
                $count = \App\AdvertisementCount::whereUserId($user->id)->whereViewDate($now_date)->whereType('show')->whereAdId($advertisement->id)->first();
                if (!$count) {
                    $all_show = 0;
                }
            }
            
            foreach ($advertisements_result as $advertisement) {
                $count = \App\AdvertisementCount::whereUserId($user->id)->whereViewDate($now_date)->whereType('show')->whereAdId($advertisement->id)->first();
                if (!$count) {
                    $new_advertisements[] = $advertisement;
                    $new_advertisements_exist[] = 0;
                } else {
                    if ($all_show) {
                        if ($count->count <= $min_count->count) {
                            $new_advertisements[] = $advertisement;
                            $new_advertisements_exist[] = 1;
                        }
                    }
                }
            }
            
            $new_advertisements_count = count($new_advertisements);
            $advertisement_index = rand(1, $new_advertisements_count);
            $advertisement = $new_advertisements[$advertisement_index - 1];
            $advertisement_exist = $new_advertisements_exist[$advertisement_index - 1];
            
            if ($advertisement_exist) {
                $advertisement_count = \App\AdvertisementCount::whereUserId($user->id)->whereViewDate($now_date)->whereType('show')->whereAdId($advertisement->id)->first();
                $advertisement_count->count = $advertisement_count->count + 1;
                $advertisement_count->save();
            } else {
                $advertisement_count = \App\AdvertisementCount::create([
                    'ad_id' => $advertisement->id,
                    'type' => 'show',
                    'user_id' => $user->id,
                    'view_date' => $now_date,
                    'count ' => 1,
                ]);
            }
        }
        
        return response()->json(['status' => 'success', 'message' => 'Advertisement!', 'id' => $advertisement->id, 'image' => $advertisement->image, 'link' => $advertisement->link], 200);
    }

    public function click(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $validation = Validator::make($request->all(), [
            'advertisement_id' => 'required'
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        $advertisement = \App\Advertisement::find(request('advertisement_id'));
        if(!$advertisement)
            return response()->json(['status' => 'fail', 'message' => 'Could not find the advertisement!', 'error_type' => 'no_advertisement'], 422);
            
        $user = JWTAuth::parseToken()->authenticate();
        $now_date = date("Y-m-d");
        $advertisement_count = \App\AdvertisementCount::whereUserId($user->id)->whereViewDate($now_date)->whereType('click')->whereAdId($advertisement->id)->first();
        if ($advertisement_count) {
            $advertisement_count->count = $advertisement_count->count + 1;
            $advertisement_count->save();
        } else {
            $advertisement_count_new = \App\AdvertisementCount::create([
                'ad_id' => $advertisement->id,
                'type' => 'click',
                'user_id' => $user->id,
                'view_date' => $now_date,
                'count ' => 1,
            ]);
        }
        
        return response()->json(['status' => 'success', 'message' => 'Advertisement Clicked!'], 200);
    }

    public function infor(Request $request) {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false], 422);
        }
        
        $now_date = date("Y-m-d");
        $year = date('Y', strtotime($now_date));
        $month = date('m', strtotime($now_date));
        $month_before = date("Y-m-d H:i:s", strtotime("$now_date  -1 month"));
        $month2_before = date("Y-m-d H:i:s", strtotime("$now_date  -2 months"));
        $month3_before = date("Y-m-d H:i:s", strtotime("$now_date  -3 months"));
        
        $ad_infor = array();
		if(request()->has('selectedAds')) {
            $advertisements = \App\Advertisement::whereIn('id', explode(',', request('selectedAds')))->get();
            foreach($advertisements as $advertisement) {
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                $show_month = $advertisement_counts->where('created_at', '>=',  $month_before)->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->sum('count');
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                $show_month2 = $advertisement_counts->where('created_at', '>=',  $month2_before)->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->sum('count');
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                $show_month3 = $advertisement_counts->where('created_at', '>=',  $month3_before)->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->sum('count');
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                if ($advertisement->start_date)
                    $advertisement_counts->where('created_at', '>=',  $advertisement->start_date);
                if ($advertisement->end_date)
                    $advertisement_counts->where('created_at', '<=',  $advertisement->end_date);
                $show_lifetime = $advertisement_counts->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->sum('count');
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                $show_year = $advertisement_counts->whereYear('view_date', '=',  $year)->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->sum('count');
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                $show_all = $advertisement_counts->whereYear('view_date', '=',  $year)->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->sum('count');
                
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                $click_month = $advertisement_counts->where('created_at', '>=',  $month_before)->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->sum('count');
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                $click_month2 = $advertisement_counts->where('created_at', '>=',  $month2_before)->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->sum('count');
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                $click_month3 = $advertisement_counts->where('created_at', '>=',  $month3_before)->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->sum('count');
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                if ($advertisement->start_date)
                    $advertisement_counts->where('created_at', '>=',  $advertisement->start_date);
                if ($advertisement->end_date)
                    $advertisement_counts->where('created_at', '<=',  $advertisement->end_date);
                $click_lifetime = $advertisement_counts->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->sum('count');
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                $click_year = $advertisement_counts->whereYear('view_date', '=',  $year)->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->sum('count');
                $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
                $click_unique = count($advertisement_counts->whereYear('view_date', '=',  $year)->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->get());
                
                $ad_infor = array('Name' => $advertisement->name, 'Link' => $advertisement->link);
                if(request()->has('show_month'))
                    $ad_infor['Total number of app visitors last 30 days'] = $show_month;
                if(request()->has('show_month2'))
                    $ad_infor['Total number of app visitors last 90 days'] = $show_month2;
                if(request()->has('show_month3'))
                    $ad_infor['Total number of app visitors last 90 days'] = $show_month3;
                if(request()->has('show_lifetime'))
                    $ad_infor['Total number of app visitors during publishing time'] = $show_lifetime;
                if(request()->has('show_year'))
                    $ad_infor['Total number of app visitors this year'] = $show_year;
                if(request()->has('show_all'))
                    $ad_infor['Total number of app visitors in total (since start of the app)'] = $show_all;
                if(request()->has('click_month'))
                    $ad_infor['Total number of clicks on banner last 30 days'] = $click_month;
                if(request()->has('click_month2'))
                    $ad_infor['Total number of clicks on banner last 60 days'] = $click_month2;
                if(request()->has('click_month3'))
                    $ad_infor['Total number of clicks on banner last 90 days'] = $click_month3;
                if(request()->has('click_lifetime'))
                    $ad_infor['Total number of clicks on banner during publishing time'] = $click_lifetime;
                if(request()->has('click_year'))
                    $ad_infor['Total number of clicks on banner this year'] = $click_year;
                if(request()->has('click_unique'))
                    $ad_infor['Total number of clicks on banner by unique visitors during publishing time'] = $click_unique;

                //$ad_infors[] = array('Name' => $advertisement->name, 'Link' => $advertisement->link, 'Total number of app visitors last 30 days' => $show_month, 'Total number of app visitors last 60 days' => $show_month2, 'Total number of app visitors last 90 days' => $show_month3, 'Total number of app visitors during publishing time' => $show_lifetime, 'Total number of app visitors this year' => $show_year, 'Total number of app visitors in total (since start of the app)' => $show_all, 'Total number of clicks on banner last 30 days' => $click_month, 'Total number of clicks on banner last 60 days' => $click_month2, 'Total number of clicks on banner last 90 days' => $click_month3, 'Total number of clicks on banner during publishing time' => $click_lifetime, 'Total number of clicks on banner this year' => $click_year, 'Total number of clicks on banner by unique visitors during publishing time' => $click_unique);
                $ad_infors[] = $ad_infor;
            }
        }
        
        return response()->json(['status' => 'success', 'message' => 'Advertisement Infor!', 'data' => $ad_infors], 200);
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
            $show_unique_count_infor = count($advertisement_counts->whereYear('view_date', '=',  $year)->whereMonth('view_date', '=',   $month_index )->where('type', '=',  'show')->get());
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $show_count_infor_all = $advertisement_counts->whereYear('view_date', '=',  $year)->whereMonth('view_date', '=',   $month_index )->where('type', '=',  'show')->sum('count');
            $show_count_infor[] = [$show_unique_count_infor, $show_count_infor_all];
            
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $click_unique_count_infor = count($advertisement_counts->whereYear('view_date', '=',  $year)->whereMonth('view_date', '=',   $month_index )->where('type', '=',  'click')->get());
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $click_count_infor_all = $advertisement_counts->whereYear('view_date', '=',  $year)->whereMonth('view_date', '=',   $month_index )->where('type', '=',  'click')->sum('count');
            $click_count_infor[] = [$click_unique_count_infor, $click_count_infor_all];
        }
        
        $statistics = [];
        $advertisements = \App\Advertisement::whereNotNull('id')->get();
        foreach ($advertisements as $advertisement) {
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $show_unique_all = count($advertisement_counts->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->get());
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $show_unique_month = count($advertisement_counts->where('created_at', '>=',  $month_before)->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->get());
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $click_unique_all = count($advertisement_counts->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->get());
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $click_unique_month = count($advertisement_counts->where('created_at', '>=',  $month_before)->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->get());
            
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $show_all = $advertisement_counts->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->sum('count');
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $show_month = $advertisement_counts->where('created_at', '>=',  $month_before)->where('type', '=',  'show')->where('ad_id', '=',  $advertisement->id)->sum('count');
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $click_all = $advertisement_counts->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->sum('count');
            $advertisement_counts = \App\AdvertisementCount::whereNotNull('id');
            $click_month = $advertisement_counts->where('created_at', '>=',  $month_before)->where('type', '=',  'click')->where('ad_id', '=',  $advertisement->id)->sum('count');
            
            $statistics[] = [$advertisement->name, $show_all, $show_unique_all, $show_month, $show_unique_month, $click_all, $click_unique_all, $click_month, $click_unique_month];
        }
        return response()->json(['status' => 'success', 'message' => 'Advertisement Overview!', 'data' => compact('show_count_infor', 'click_count_infor', 'statistics')], 200);
    }
}