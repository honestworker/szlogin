<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'status', 'password', 'backend',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function simple_profile()
    {
        return $this->hasOne('App\Profile', 'user_id', 'id')->select('id', 'user_id', 'full_name', 'avatar', 'street_address');
    }
    
    public function sound_profile()
    {
        return $this->hasOne('App\Profile', 'user_id', 'id')->select('id', 'user_id', 'os_type', 'sound', 'vibration', 'language', 'push_token');
    }
    
    public function profile()
    {
        return $this->hasOne('App\Profile', 'user_id', 'id')->select('id', 'user_id', 'full_name', 'avatar', 'street_address', 'postal_code', 'country', 'language', 'push_token', 'created_at');
    }
    
    public function notification()
    {
        return $this->hasMany('App\Notification', 'user_id', 'id');
    }

    public function groups()
    {
        return $this->hasMany('App\GroupUser', 'user_id', 'id')->select('id','user_id', 'group_id', 'admin', 'status')->where('status', 'activated')->with('group');
    }
}