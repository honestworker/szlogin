<?php
namespace App;
use Eloquent;

class Profile extends Eloquent {
    
    protected $fillable = [
                            'user_id',
                            
                            // 'group_name',
                            // 'org_num',
                            // 'contact_person',
                            'phone_number',
                            
                            'group_id',
                            
                            'first_name',
                            'family_name',
                            'full_name',
                            
                            'avatar',
                            'street_address',
                            'street_name',
                            'postal_code',
                            'country',
                            'city',
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
    
    public function roles()
    {
        return $this->hasMany('App\UserRole', 'user_id', 'user_id');
    }
}