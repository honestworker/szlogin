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
        return $this->belongsTo('App\Profile', 'user_id', 'user_id')->select('id');
    }
    
    public function images()
    {
        return $this->hasMany('App\Image', 'parent_id', 'id')->where('type', '=', 'notification');
    }
    
    public function comments()
    {
        return $this->hasMany('App\Comment', 'notification_id', 'id');
    }
    
    public function group()
    {
        return $this->belongsTo('App\Group', 'id', 'group_id');
    }
}