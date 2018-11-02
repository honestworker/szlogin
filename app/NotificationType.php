<?php
namespace App;
use Eloquent;

class NotificationType extends Eloquent {
    
	protected $fillable = [
							'name', 'trans_name', 'status',
						];
	protected $primaryKey = 'id';
	protected $table = 'notification_type';
}