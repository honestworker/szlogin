<?php
namespace App;
use Eloquent;

class UserRole extends Eloquent {
	protected $fillable = [
                            'user_id',
                            'role_id',
						];
	protected $primaryKey = 'id';
	protected $table = 'user_roles';
	
    public function profile()
    {
        return $this->belongsToMany('App\UserRole', 'user_id', 'user_id');
    }
}