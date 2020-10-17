<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $guarded = [
    ];
    public function assign()
    {
       return $this->hasOne('App\AssignBook');
    }
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->book_id = $model->category .'-'. $model->id;
            $model->save();
        });
    }
}
