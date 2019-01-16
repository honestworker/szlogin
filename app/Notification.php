<?php
namespace App;
use Eloquent;

class Notification extends Eloquent {
    
    protected $fillable = [
                            'user_id',
                            'group_id',
                            'contents',
                            'type',
                            'status',
                        ];
    protected $primaryKey = 'id';
    protected $table = 'notifications';
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id')->select('id', 'email');
    }
    
    public function profile()
    {
        return $this->belongsTo('App\Profile', 'user_id', 'user_id')->select('id', 'full_name');
    }
    
    public function images()
    {
        return $this->hasMany('App\Image', 'parent_id', 'id')->where('type', '=', 'notification')->select('id', 'url', 'width', 'height', 'type', 'parent_id');
    }
    
    public function comments()
    {
        return $this->hasMany('App\Comment', 'notification_id', 'id')->select('id', 'user_id', 'notification_id', 'contents', 'status', 'created_at');
    }
    
    public function group()
    {
        return $this->belongsTo('App\Group', 'group_id', 'id')->select('id', 'group_id');
    }

    public function type()
    {
        return $this->belongsTo('App\NotificationType', 'type', 'id')->select('id', 'name', 'image');
    }
}