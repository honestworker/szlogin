<?php
namespace App;
use Eloquent;

class Role extends Eloquent {
	protected $fillable = [
							'name',
						];
	protected $primaryKey = 'id';
	protected $table = 'roles';
}