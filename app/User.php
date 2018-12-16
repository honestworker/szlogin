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
        'first_name', 'family_name', 'email', 'status', 'avatar', 'password', 'backend', 'alarms', 'push_token',
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
        return $this->hasOne('App\Profile', 'user_id', 'id')->select('id', 'user_id', 'first_name', 'family_name', 'avatar', 'street_address');
    }
    
    public function sound_profile()
    {
        return $this->hasOne('App\Profile', 'user_id', 'id')->select('id', 'user_id', 'os_type', 'sound', 'vibration', 'language');
    }
    
    public function profile()
    {
        return $this->hasOne('App\Profile', 'user_id', 'id')->select('id', 'user_id', 'group_id', 'phone_number', 'first_name', 'family_name', 'full_name', 'avatar', 'street_address', 'postal_code', 'country', 'city', 'language',  'is_admin', 'created_at');
    }
    
    public function profile_short()
    {
        return $this->hasOne('App\Profile', 'user_id', 'id');
    }
    
    public function notification()
    {
        return $this->hasMany('App\Notification', 'user_id', 'id');
    }

    public function groups()
    {
        return $this->hasOne('App\UserGroups', 'user_id', 'id');
    }
}