<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('films', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name');
            $table->integer('duration');
            $table->string('tahun_produksi');
            $table->string('subtitle');
            $table->longText('sinopsis');
            $table->string('sutradara');
            $table->string('produser');
            $table->string('penulis');
            $table->string('poster');
            $table->text('gsm');
            $table->string('trailer');
            $table->string('film');
            $table->string('kru');
            $table->string('other_1')->nullable();
            $table->string('status')->default('submitted');
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
        Schema::dropIfExists('films');
    }
}
