<?php
namespace App;
use Eloquent;

class Setting extends Eloquent {

	protected $fillable = [
							'name',
							'value',
						];
	protected $primaryKey = 'id';
	protected $table = 'options';
	public $timestamps = false;
}