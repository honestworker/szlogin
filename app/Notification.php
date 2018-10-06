<?php
namespace App;
use Eloquent;

class Notification extends Eloquent {
    
    protected $fillable = [
                            'user_id',
                            'group_id',
                            'title',
                            'contents',
                            'type',
                            'status',
                        ];
    protected $primaryKey = 'id';
    protected $table = 'notifications';
    
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    
    public function image()
    {
        return $this->hasMany('App\Image');
    }
    
    public function group()
    {
        return $this->belongsTo('App\Group');
    }
}