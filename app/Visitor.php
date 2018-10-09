<?php
namespace App;
use Eloquent;

class Visitor extends Eloquent {
	protected $fillable = [
                            'year',
                            'month',
                            'value',
						];
	protected $primaryKey = 'id';
	protected $table = 'visitors';
}