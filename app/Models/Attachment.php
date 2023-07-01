<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable=[
        'attachmentable_type','attachmentable_id','file_name','client_name','attribute'
    ];
    public function attachmentable()
    {
        return $this->morphTo();
    }
}
