<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, SoftDeletes;
    
    protected $table = "user";
    protected $fillable = [
        'username', 
        'email', 
        'password',
        'role',
        'token',
    ];

    
    protected $hidden = [
        'password',
    ];
    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    
    
    public function getJWTCustomClaims()
    {
        return [];
    }
    
    public function lendings(){
        return $this->hasMany(lending::class);
    }

    public function restoration(){
        return $this->hasMany(Restoration::class);
    }

   
}
//return $this->belongsTo(Stuff::class, 'kolom fk' , 'kolom pk');
//saat penulisan column tidak sesuai dengan aturan menulis