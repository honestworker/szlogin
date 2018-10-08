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
    protected $table = 'notifications';
    
    public function notificaion()
    {
        return $this->belongsTo('App\Notification', 'notification_id', 'id');
    }
    
    public function image()
    {
        return $this->hasMany('App\Image');
    }
}