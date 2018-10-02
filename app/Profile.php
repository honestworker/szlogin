<?php
namespace App;
use Eloquent;

class Profile extends Eloquent {
    
    protected $fillable = [
                            'user_id',
                            'group_id',
                            'group_names',
                            'org_num',
                            'contact_person',
                            'phone_number',
                            'first_name',
                            'last_name',
                            'avatar'
                        ];
    protected $primaryKey = 'id';
    protected $table = 'profiles';
    
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    
    public function group()
    {
        return $this->hasOne('App\Group', 'id', 'group_id');
    }
    
    public function role()
    {
        return $this->hasOne('App\Role', 'id', 'role_id');
    }
}