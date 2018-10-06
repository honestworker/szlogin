<?php
namespace App;
use Eloquent;

class Image extends Eloquent {
    
    protected $fillable = [
                            'parent_id',
                            'url',
                        ];
    protected $primaryKey = 'id';
    protected $table = 'images';
    
    public function notification()
    {
        return $this->belongsTo('App\Notification');
    }
}