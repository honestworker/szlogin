<?php
namespace App;
use Eloquent;

class Advertisement extends Eloquent {
    
	protected $fillable = [
							'image',
							'link',
							'country',
							'show_count',
							'link_count',
							'status',
						];
	protected $primaryKey = 'id';
	protected $table = 'ads';
}