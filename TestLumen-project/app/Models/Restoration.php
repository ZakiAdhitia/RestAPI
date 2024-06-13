<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Restoration extends Model
{
    //protected $primarykey = no;
    //protected $timestamps = false;
    //protected $table = 'inbound_stuffs';
    use SoftDeletes; // optional digunakan hanya untuk table yang menggunakan sofdeletes
    protected $fillable = [
        'user_id',
        'lending_id',
        'date_time',
        'total_good_stuff',
        'total_defec_stuff'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function lending(){
        return belongsTo(Lending::class);
    }
}
//return $this->belongsTo(Stuff::class, 'kolom fk' , 'kolom pk');
//saat penulisan column tidak sesuai dengan aturan menulis
