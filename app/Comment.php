<?php
namespace App;
use Eloquent;

class Comment extends Eloquent {
    
    protected $fillable = [
                            'notificaion_id',
                            'user_id',
                            'title',
                            'contents',
                            'status',
                        ];
    protected $primaryKey = 'id';
    protected $table = 'comments';
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id')->select('id', 'email');
    }
    
    public function profile()
    {
        return $this->belongsTo('App\Profile', 'user_id', 'user_id');
    }
    
    public function notification()
    {
        return $this->belongsTo('App\Notification', 'id', 'notification_id');
    }
    
    public function images()
    {
        return $this->hasMany('App\Image', 'parent_id', 'id')->where('type', '=', 'comment')->select('id', 'url', 'width', 'height', 'type', 'parent_id', 'created_at');
    }
}