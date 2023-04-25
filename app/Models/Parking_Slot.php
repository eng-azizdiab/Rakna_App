<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parking_Slot extends Model
{
    protected $table='parking_slots';
    use HasFactory;
    protected $guarded=[];

    public function reservations(){
        return $this->hasMany(Reservation::class,'parking_slot_id','id');
    }
    public function parking(){
        return $this->belongsTo(Parking::class);
    }
}
