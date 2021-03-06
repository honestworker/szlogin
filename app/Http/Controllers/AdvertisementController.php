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
    protected $image_extensions = array('jpeg', 'png', 'jpg', 'gif');

    public function index(){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
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
        
        if(request()->has('min_postal'))
            $advertisements->where('min_postal', '>=', request('min_postal'));
        
        if(request()->has('max_postal'))
            $advertisements->where('max_postal', '<=', request('max_postal'));
        
        $advertisements->select('id', 'country', 'image', 'link', 'name', 'start_date', 'end_date', 'status', \DB::raw('(select count(*) from ads_count where ads_count.ad_id = ads.id and ads_count.type = \'show\') as show_count'), \DB::raw('(select sum(count) from ads_count where ads_count.ad_id = ads.id and ads_count.type = \'show\') as show_sum'), \DB::raw('(select count(*) from ads_count where ads_count.ad_id = ads.id and ads_count.type = \'click\') as click_count'), \DB::raw('(select sum(count) from ads_count where ads_count.ad_id = ads.id and ads_count.type = \'click\') as click_sum'));
        
        //$advertisements->orderBy(request('sortBy'), request('order'));
        
        return $advertisements->paginate(request('pageLength'));
    }

    private function initUserAdsCount(){
        $now_date = date("Y-m-d");
        $user_ads_count = \App\UserAdsCount::whereViewDate($now_date)->get();
        foreach ($user_ads_count as $user_ad_count) {
            $user_ad_count->count = 0;
            $user_ad_count->save();
        }
    }

    public function store(Request $request){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        if (!$profile)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find user profile!', 'data' => null, 'error_type' => 'no_profile'], 422);
            
        $validation = Validator::make($request->all(), [
            'name' => 'required|min:1',
            'link' => 'required|min:1',
            'country' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        if ($request->has('start_date')) {
            $validation = Validator::make($request->all(), [
                'start_date' => 'date_format:"Y-m-d"',
            ]);
            if($validation->fails())
                return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_start_date_format'], 422);
        }
        if ($request->has('end_date')) {
            $validation = Validator::make($request->all(), [
                'end_date' => 'date_format:"Y-m-d"',
            ]);
            if($validation->fails())
                return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_end_date_format'], 422);
        }
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array(strtolower($extension), $this->image_extensions)) {
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg, gif!'], 422);
                }
            }
        }
        if ($request->has('min_postal')) {
            $validation = Validator::make($request->all(), [
                'min_postal' => 'max:30',
            ]);
            if($validation->fails())
                return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_min_postal'], 422);
        }
        if ($request->has('max_postal')) {
            $validation = Validator::make($request->all(), [
                'max_postal' => 'max:30',
            ]);
            if($validation->fails())
                return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_max_postal'], 422);
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
        if ($request->has('min_postal')) {
            if (!is_null(request('min_postal'))) {
                $advertisement->min_postal = request('min_postal');
            } else {
                $advertisement->min_postal = '';
            }
        } else {
            $advertisement->min_postal = '';
        }
        if ($request->has('max_postal')) {
            if (!is_null(request('max_postal'))) {
                $advertisement->max_postal = request('max_postal');
            } else {
                $advertisement->max_postal = '';
            }
        } else {
            $advertisement->max_postal = '';
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
        
        $this->initUserAdsCount();

        return response()->json(['status' => 'success', 'message' => 'Aadvertisement has created succesfully!'], 200);
    }

    public function destroy(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        $advertisement = \App\Advertisement::find($id);
        
        if(!$advertisement)
            return response()->json(['message' => 'Couldnot find Advertisement!'], 422);
            
        $advertisement->delete();
        
        $this->initUserAdsCount();
        
        return response()->json(['message' => 'Advertisement deleted!']);
    }

    public function show($id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $advertisement = \App\Advertisement::whereId($id)->first();
        
        if(!$advertisement)
            return response()->json(['message' => 'Couldnot find Advertisement!'], 422);
            
        return $advertisement;
    }

    public function update(Request $request, $id){
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $advertisement = \App\Advertisement::whereId($id)->first();
        
        if(!$advertisement)
            return response()->json(['status' => 'fail', 'message' => 'Could not find the advertisement!'], 422);
        
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'link' => 'required',
            'country' => 'required',
        ]);
        if($validation->fails())
            return response()->json(['message' => $validation->messages()->first(), 'error_type' => 'no_fill'], 422);
            
        if ($request->has('start_date')) {
            $validation = Validator::make($request->all(), [
                'start_date' => 'date_format:"Y-m-d"',
            ]);
            if($validation->fails())
                return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_start_date_format'], 422);
        }
        if ($request->has('end_date')) {
            $validation = Validator::make($request->all(), [
                'end_date' => 'date_format:"Y-m-d"',
            ]);
            if($validation->fails())
                return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_end_date_format'], 422);
        }
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array(strtolower($extension), $this->image_extensions)) {
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg, gif!'], 422);
                }
            }
        }
        if ($request->has('min_postal')) {
            $validation = Validator::make($request->all(), [
                'min_postal' => 'max:30',
            ]);
            if($validation->fails())
                return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_min_postal'], 422);
        }
        if ($request->has('max_postal')) {
            $validation = Validator::make($request->all(), [
                'max_postal' => 'max:30',
            ]);
            if($validation->fails())
                return response()->json(['status' => 'fail', 'message' => $validation->messages()->first(), 'error_type' => 'no_max_postal'], 422);
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
        if ($request->has('min_postal')) {
            if (!is_null(request('min_postal'))) {
                $advertisement->min_postal = request('min_postal');
            } else {
                $advertisement->min_postal = '';
            }
        } else {
            $advertisement->min_postal = '';
        }
        if ($request->has('max_postal')) {
            if (!is_null(request('max_postal'))) {
                $advertisement->max_postal = request('max_postal');
            } else {
                $advertisement->max_postal = '';
            }
        } else {
            $advertisement->max_postal = '';
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
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json(['status' => 'fail', 'authenticated' => false, 'error_type' => 'token_error'], 422);
        }
        
        $advertisement = \App\Advertisement::find(request('id'));
        
        if(!$advertisement)
            return response()->json(['message' => 'Couldnot find Advertisement!'], 422);
            
        $advertisement->status = !$advertisement->status;
        $advertisement->save();
        
        return response()->json(['message' => 'Advertisement updated!']);
    }

    public function infor(Request $request){
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
        
        $ad_infors = array();
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

    public function overview(Request $request){
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