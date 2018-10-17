<?php
namespace App;
use Eloquent;

class UserGroups extends Eloquent {
    
    protected $fillable = [
                            'user_id',
                            'group_id',
                        ];
    protected $primaryKey = 'id';
    protected $table = 'user_groups';
}