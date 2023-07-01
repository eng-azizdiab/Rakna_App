<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Interfaces\AttachmentsManagerInterface;
use App\Http\Traits\AttachmentsManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Cashier\Billable;


class User extends Authenticatable implements JWTSubject,AttachmentsManagerInterface
{
    use HasApiTokens, HasFactory, Notifiable,Billable,AttachmentsManager;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function car(){
        return $this->hasMany(Car::class,'user_id','id');
    }
    public function payment(){
        return $this->hasMany(Payment::class,'user_id','id');
    }
    public function reservations(){
        return $this->hasMany(Reservation::class,'user_id','id');
    }

    public function getFolderName(): string
    {
        return $this->getTable();
    }

    public function attachments()
    {
        return $this->morphMany('App\Models\Attachment', 'attachmentable');
    }

    public function setUserFileAttribute(File|UploadedFile $file)
    {

        $lastAttachment = $this->latest_attachment()->value('file_name');

        $attachment = $this->uploadAttachment($file, $lastAttachment);
        $this->attachments()->updateOrCreate([
            'attachmentable_id'=>$this->id,
            'attachmentable_type'=>self::class,
        ],[
            'file_name'=>$attachment,
            'client_name'=>$file->getClientOriginalName()
        ]);
    }
    public function latest_attachment(){
        return $this->attachments()->where('attribute', '=',null)->latest();
    }
    public function getUserFileUrlAttribute()
    {
        return $this->getAttachment($this->latest_attachment()->value('file_name'));
    }


}
