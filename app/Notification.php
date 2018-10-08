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
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
    
    public function image()
    {
        return $this->hasMany('App\Image', 'parent_id', 'id');
    }
    
    public function comment()
    {
        return $this->hasMany('App\Comment', 'id', 'notification_id');
    }
    
    public function group()
    {
        return $this->belongsTo('App\Group', 'id', 'group_id');
    }
}