<?php
namespace App;
use Eloquent;

class Country extends Eloquent {
    
	protected $fillable = [
							'idx',
							'name',
						];
	protected $primaryKey = 'id';
	protected $table = 'country';
}