<?php
namespace App;
use Eloquent;

class AdvertisementCount extends Eloquent {
    
	protected $fillable = [
							'ad_id',
							'type',
							'user_id',
							'count',
							'view_date',
						];
	protected $primaryKey = 'id';
	protected $table = 'ads_count';
}