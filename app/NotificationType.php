<?php
namespace App;
use Eloquent;

class NotificationType extends Eloquent {
    
	protected $fillable = [
							'name',
						];
	protected $primaryKey = 'id';
	protected $table = 'notification_type';
}