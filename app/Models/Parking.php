<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Parking extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $geometry = ['location'];
    protected $geometryAsText = true;

    public function newQuery($excludeDeleted = true)
    {
        if (!empty($this->geometry) && $this->geometryAsText === true)
        {
            $raw = '';
            foreach ($this->geometry as $column)
            {
                $raw .= 'AsText(`' . $this->table . '`.`' . $column . '`) as `' . $column . '`, ';
            }
            $raw = substr($raw, 0, -2);

            return parent::newQuery($excludeDeleted)->addSelect('*', DB::raw($raw));
        }

        return parent::newQuery($excludeDeleted);
    }

    public function __toString()
    {
        return "ST_X($this->location) as latitude, ST_Y($this->location) as longitude";
    }
    public function payments(){
        return $this->hasMany(Payment::class,'parking_id','id');
    }
    public function parkingSlots(){
        return $this->hasMany(Parking_Slot::class,'parking_id','id');
    }

    public function reservations(){
        return $this->hasMany(Reservation::class,'parking_id','id');
    }
    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }
}
