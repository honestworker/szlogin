<?php
namespace App;
use Eloquent;

class GroupUser extends Eloquent {
    
	protected $fillable = [
							'group_id',
							'user_id',
							'admin',
							'status',
						];
	protected $primaryKey = 'id';
    protected $table = 'group_users';
    
    public function group()
    {
        return $this->hasOne('App\Group', 'id', 'group_id')->select('id', 'name', 'postal_code')->where('status', 1);
	}
	
    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id')->select('id', 'email')->where('backend', 0);
    }
}