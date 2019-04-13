<?php
namespace App;
use Eloquent;

class NotificationUnread extends Eloquent {
    
    protected $fillable = [
                            'user_id',
                            'notification_id',
                        ];
    protected $primaryKey = 'id';
    protected $table = 'notification_unreads';
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id')->select('id', 'email');
    }

    public function notification()
    {
        return $this->belongsTo('App\Notification', 'notification_id', 'id')->select('id', 'group_id', 'type', 'country', 'contents');
    }
}