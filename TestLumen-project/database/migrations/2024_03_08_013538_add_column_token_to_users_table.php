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
        Schema::table('user', function (Blueprint $table) {
            $table->string('token')->nullable(); //nullable 
            // $table->renameColumn('token', 'api token') mengubah nama kolom dari token ke api token

          // $table->text('token')->change(); mengganti tipe data dari string ke text 
          # lalu diikuti method change karena merubah bukan menambah

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('token'); //dropColumn 
            // $table->renameColumn('api token,token') mengubah nama kolom dari token ke api token
        });
    }
};
