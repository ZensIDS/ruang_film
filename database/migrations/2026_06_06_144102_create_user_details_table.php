<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('community_name')->nullable();
            $table->string('provinsi_code');
            $table->string('provinsi_name');
            $table->string('kabupaten_code');
            $table->string('kabupaten_name');
            $table->string('kecamatan_code');
            $table->string('kecamatan_name');
            $table->string('desa_code');
            $table->string('desa_name');
            $table->string('username_ig')->nullable();
            $table->string('posisi')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_details');
    }
}
