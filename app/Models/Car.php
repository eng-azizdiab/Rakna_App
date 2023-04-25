<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function carRecievers(){
        return $this->hasMany(Car_reciever::class,'car_id','id');
    }
    public function reservations(){
        return $this->hasMany(Reservation::class,'car_id','id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
