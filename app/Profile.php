<?php
namespace App;
use Eloquent;

class Profile extends Eloquent {
    
    protected $fillable = [
                            'user_id',
                            
                            'phone_number',
                            
                            'group_id',
                            
                            'full_name',
                            
                            'avatar',
                            'street_address',
                            'postal_code',
                            'country',
                            'city',
                            'is_admin',
                            
                            'os_type',
                            'sound',
                            'vibration',
                            
                            'language'
                        ];
    protected $primaryKey = 'id';
    protected $table = 'profiles';
    
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    
    public function notification()
    {
        return $this->hasMany('App\Notification', 'user_id', 'user_id');
    }
    
    public function group()
    {
        return $this->hasOne('App\Group', 'id', 'group_id');
    }
}