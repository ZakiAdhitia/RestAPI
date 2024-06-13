<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stuffs', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->enum("category", ['HTL', 'KLN', 'Teknisi/Sarpras']);
            $table->timestamps();
            $table->softDeletes();
        });
    }
    //mendefinisikan perubahan yang dilakukan pada skema database (menambah tabel, kolom, atau index pada tabel)
    //method up() berjalan ketika menjalankan php artisan migrate 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stuffs');
    }
    //mendefinisikan pembatalan perintah perubahan yang 
};
