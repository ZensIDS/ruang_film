<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewRubricsTable extends Migration
{
    public function up()
    {
        Schema::create('review_rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('stage');
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category_id', 'stage']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_rubrics');
    }
}
