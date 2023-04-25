<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function payement(){
        return $this->hasOne(Payment::class,'reservation_id','id');
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function car(){
        return $this->belongsTo(Car::class);
    }
    public function parking(){
        return $this->belongsTo(Parking::class);
    }
    public function parkingSlot(){
        return $this->belongsTo(Parking_Slot::class);
    }
}
