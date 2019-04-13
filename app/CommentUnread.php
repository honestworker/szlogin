<?php
namespace App;
use Eloquent;

class CommentUnread extends Eloquent {
    
    protected $fillable = [
                            'user_id',
                            'comment_id',
                            'notification_id',
                        ];
    protected $primaryKey = 'id';
    protected $table = 'comment_unreads';
    
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id')->select('id', 'email');
    }

    public function comment()
    {
        return $this->belongsTo('App\Comment', 'comment_id', 'id')->select('id', 'notification_id', 'contents');
    }
}