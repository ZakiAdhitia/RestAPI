<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stuffstock extends Model
{
    //protected $primarykey = no;
    //protected $timestamps = false;
    //protected $table = 'inbound_stuffs';
    use SoftDeletes; // optional digunakan hanya untuk table yang menggunakan sofdeletes
    protected $fillable = ['stuff_id' , 'total_available' , 'total_defec'];

    public function stuff(){
        return $this->belongsTo(Stuff::class);
    }
}
//return $this->belongsTo(Stuff::class, 'kolom fk' , 'kolom pk');
//saat penulisan column tidak sesuai dengan aturan menulis