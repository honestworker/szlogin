<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class NotificationController extends Controller
{
    protected $images_path = 'images/notifications/';
    
    protected $image_extensions = array('jpeg', 'png', 'jpg', 'gif', 'svg');
    
	public function index() {
		$notifications = \App\Notification::with('user', 'group');
		
		if(request()->has('title'))
			$notifications->where('title', 'like', '%'.request('title').'%');
			
        if(request()->has('contents'))
            $notifications->where('contents', 'like', '%'.request('contents').'%');
            
        if(request()->has('first_name')) {
            $notifications->whereHas('user', function($q) {
                $q->where('first_name', 'like', '%'.request('first_name').'%');
            });
        }
        
        if(request()->has('family_name')) {
            $notifications->whereHas('user', function($q) {
                $q->where('family_name', 'like', '%'.request('family_name').'%');
            });
        }
        
        if(request()->has('status'))
            $notifications->whereStatus(request('status'));
        
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'status' || request('sortBy') == 'created_at')
                $notifications->orderBy(request('sortBy'), request('order'));
            else if(request('sortBy') == 'first_name' || request('sortBy') == 'family_name' || request('sortBy') == 'phone_number')
                $notifications->with(['user' => function ($q) {
                    $q->orderBy(request('sortBy'), request('order'));
                }]);
        }
        
		return $notifications->paginate(request('pageLength'));
	}
	
	// Notification
    public function createNotification(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $validation = Validator::make($request->all(), [
            'type' => 'required',
            'contents' => 'required|min:1',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $group = \App\Group::find($profile->group_id);
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'You must be any group memeber!'], 422);
        
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array($extension, $this->image_extensions)) {
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg, gif, svg!'], 422);
                }
            }
        }
        $notification = new \App\Notification;
        $notification->fill(request()->all());
        $notification->user_id = $user->id;
        $notification->group_id = $group->id;
        $notification->status = 1;
        $notification->save();
        
        $file_count = 0;
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $file_count = $file_count + 1;
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
                
                $notificaion_image = new \App\Image;
                $notificaion_image->parent_id = $notification->id;
                $notificaion_image->url = $file_name;
                $notificaion_image->save();
            }
        }
        
        return response()->json(['status' => 'success', 'message' => 'Notification has created succesfully!', 'file_count' => $file_count], 200);
    }
    
    public function getNotification(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
            
        $group = \App\Group::find($profile->group_id);
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'You must be any group memeber!'], 422);
        
        
        $notification = \App\Notification::with('user.profile', 'comment');
        // $notification->with(['user.profile' => function ($q) {
        //     $q->select('first_name');
        // }]);
        $notification->with(['user' => function ($q) {
            $q->select('id', 'email');
        }]);
        $notification->whereStatus(1);
        $notification->where('group_id', '=', $group->id);
        $notification->orderBy('updated_at', 'DESC');
        
        return response()->json(['status' => 'success', 'message' => 'Get Notification Data Successfully!', 'notifications' => $notification->get()], 200);
    }

    public function toggleStatus(Request $request){
        $notificaioin = \App\Notification::find($request->input('id'));
        
        if(!$notificaioin)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notificaioin!'], 422);
            
        $notificaioin->status = !$notificaioin->status;
        $notificaioin->save();
        
        return response()->json(['status' => 'success', 'message' => 'Notification updated!'], 200);
    }
    
	// Comments
    public function createComment(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $profile = $user->Profile;
        
        $validation = Validator::make($request->all(), [
            'notificaion_id' => 'required',
            'title' => 'required|min:1',
            'contents' => 'required',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $group = \App\Group::find($profile->group_id);
        if(!$group)
            return response()->json(['status' => 'fail', 'message' => 'You must be any group memeber!'], 422);
        
        if($request->input('notificaion_id') == 0) {
            return response()->json(['status' => 'fail', 'message' => 'You must specify the notificaion!'], 422);
        }
        
        $notification = \App\Notification::find($request->input('notificaion_id'));
        if(!$notification)
            return response()->json(['status' => 'fail', 'message' => 'You specify the empty notification!'], 422);
            
        
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                if (!in_array($extension, $this->image_extensions)) {
                    return response()->json(['status' => 'fail', 'message' => 'Your images must be jpeg, png, jpg, gif, svg!'], 422);
                }
            }
        }
        
        $comment = new \App\Comment;
        $comment->fill(request()->all());
        $comment->user_id = $user->id;
        $comment->status = 1;
        $comment->save();
        
        if($request->hasfile('images')) {
            foreach($request->file('images') as $image)
            {
                $extension = $image->getClientOriginalExtension();
                $mt = explode(' ', microtime());
                $name = ((int)$mt[1]) * 1000000 + ((int)round($mt[0] * 1000000));
                $file_name = $name . '.' . $extension;
                $image->move($this->images_path, $file_name);
                
                $comment_image = new \App\Image;
                $comment_image->parent_id = $comment->id;
                $comment_image->url = $file_name;
                $comment_image->save();
            }
        }
        
        return response()->json(['status' => 'success', 'message' => 'Comment has created succesfully!'], 200);
    }

    public function toggleCommentStatus(Request $request){
        $comment = \App\Comment::find($request->input('id'));
        
        if(!$comment)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find comment!'], 422);
            
        $comment->status = !$comment->status;
        $comment->save();
        
        return response()->json(['status' => 'success', 'message' => 'Comment updated!'], 200);
    }
    
    // Notification Type
	public function indexType(){
		$notification_type = \App\NotificationType::whereNotNull('id');
		
		if(request()->has('name'))
			$notification_type->where('name', 'like', '%'.request('name').'%');
		
        if(request()->has('status'))
            $notification_type->whereStatus(request('status'));
        
        if(request()->has('sortBy') && request()->has('order')) {
            if(request('sortBy') == 'name' || request('sortBy') == 'created_at')
                $notification_type->orderBy(request('sortBy'), request('order'));
        }
        
		return $notification_type->paginate(request('pageLength'));
	}

	public function allType(){
		$notification_type = \App\NotificationType::whereNotNull('id');
		
        $notification_type->whereStatus(1);
        $notification_type->orderBy('name', 'ASC');
        $countries = $notification_type->pluck('name');
        
		return response()->json(['status' => 'success', 'message' => 'Get Notification Type Data Successfully!', 'types' => $countries], 200);
	}

    public function storeType(Request $request){
        
        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:notification_type',
        ]);
        
        if($validation->fails())
        	return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
        
        $notification_type = new \App\NotificationType;
        $notification_type->fill(request()->all());
        $notification_type->name = request('name');
        $notification_type->status = 1;
        $notification_type->save();
        
        return response()->json(['status' => 'success', 'message' => 'Notification Type added!', 'data' => $notification_type], 200);
    }

    public function destroyType(Request $request, $id){
        $notification_type = \App\NotificationType::find($id);
        
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!'], 422);
            
        $notification_type->delete();
        
        return response()->json(['status' => 'success', 'message' => 'Notification Type deleted!'], 200);
    }

    public function showType($idx){
        $notification_type = \App\NotificationType::whereIdx($idx)->first();
        
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!'], 422);
            
        return $notification_type;
    }

    public function updateType(Request $request, $id){
        
        $notification_type = \App\NotificationType::whereId($id)->first();
        
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!'], 422);
            
        $validation = Validator::make($request->all(), [
            'name' => 'required|unique:notification_type,name,'.$notification_type->id.',id',
        ]);
        
        if($validation->fails())
            return response()->json(['status' => 'fail', 'message' => $validation->messages()->first()], 422);
            
        $notification_type->name = request('name');
        $notification_type->save();
        return response()->json(['status' => 'success', 'message' => 'Notification Type updated!', 'data' => $notification_type], 200);
    }

    public function toggleTypeStatus(Request $request){
        $notification_type = \App\NotificationType::find($request->input('id'));
        
        if(!$notification_type)
            return response()->json(['status' => 'fail', 'message' => 'Couldnot find notification type!'], 422);
            
        $notification_type->status = !$notification_type->status;
        $notification_type->save();
        
        return response()->json(['status' => 'success', 'message' => 'Notification Type updated!'], 200);
    }
    
}