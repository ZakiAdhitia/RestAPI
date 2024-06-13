<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class InboundStuff extends Model
{
    //protected $primarykey = no;
    //protected $timestamps = false;
    //protected $table = 'inbound_stuffs'; jika table salah tulis 
    use SoftDeletes;
    protected $fillable = [
    'stuff_id' ,
    'total',
    'date',
    'proff_file'
];

    public function stuff(){
        return $this->belongsTo(Stuff::class);
    }

    
}

// protected $fillabel berfungsi untuk menentukan column mana saja yang akan / dapat diisi
//return $this->belongsTo(Stuff::class, 'kolom fk' , 'kolom pk');
//saat penulisan column tidak sesuai dengan aturan menulis