<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stuff extends Model
{
    //protected $primarykey = no;
    //protected $timestamps = false;
    //protected $table = 'inbound_stuffs';
    use SoftDeletes; // optional digunakan hanya untuk table yang menggunakan sofdeletes
    protected $fillable = ["name" , "category"];

    public function stuffStock(){
        return $this->hasOne(StuffStock::class);
    }

    public function inboundStuffs(){
        return $this->hasMany(InboundStuff::class);
    }

    public function lendings(){
        return $this->hasMany(lending::class);
    }
}
//return $this->belongsTo(Stuff::class, 'kolom fk' , 'kolom pk');
//saat penulisan column tidak sesuai dengan aturan menulis