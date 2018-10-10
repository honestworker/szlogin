<?php
namespace App;
use Eloquent;

class AdvertisementCount extends Eloquent {
    
	protected $fillable = [
							'ad_id',
							'type',
						];
	protected $primaryKey = 'id';
	protected $table = 'ads_count';
}