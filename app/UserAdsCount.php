<?php
namespace App;
use Eloquent;

class UserAdsCount extends Eloquent {
    
	protected $fillable = [
							'ad_id',
							'user_id',
							'count',
							'view_date',
						];
	protected $primaryKey = 'id';
	protected $table = 'user_ads_count';
}