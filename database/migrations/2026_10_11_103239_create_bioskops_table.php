<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBioskopsTable extends Migration
{
    public function up()
    {
        Schema::create('bioskops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('venue')->nullable();
            $table->string('address')->nullable();
            $table->date('show_date')->nullable();
            // [{label: "Sesi 1", time: "13:00", status: "available|full"}, ...]
            $table->json('sessions')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bioskops');
    }
}