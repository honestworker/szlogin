<?php
namespace App;
use Eloquent;

class Group extends Eloquent {
	protected $fillable = [
							'name',
							'description',
						];
	protected $primaryKey = 'id';
	protected $table = 'groups';
}