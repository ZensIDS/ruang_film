<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchandiseVariantsTable extends Migration
{
    public function up()
    {
        Schema::create('merchandise_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchandise_id')->constrained('merchandises')->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedInteger('qty_stock')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['merchandise_id', 'name']);
        });

        Schema::table('merchandises', function (Blueprint $table) {
            $table->boolean('has_variants')->default(false)->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('merchandises', function (Blueprint $table) {
            $table->dropColumn('has_variants');
        });

        Schema::dropIfExists('merchandise_variants');
    }
}