<?php
namespace App;
use Eloquent;

class Group extends Eloquent {
    
	protected $fillable = [
							'name',
							'postal_code',
							'country',
						];
	protected $primaryKey = 'id';
	protected $table = 'groups';
}