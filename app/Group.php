<?php
namespace App;
use Eloquent;

class Group extends Eloquent {
    
	protected $fillable = [
							'group_id',
							'org_number',
							'contact_person',
							'org_name',
							'email',
							'mobile_number',
							'country',
							'description',
							'name',
							'description',
						];
	protected $primaryKey = 'id';
	protected $table = 'groups';
}