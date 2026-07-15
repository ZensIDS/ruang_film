<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewRubricItemsTable extends Migration
{
    public function up()
    {
        Schema::create('review_rubric_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_rubric_group_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('weight', 8, 2)->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_rubric_items');
    }
}
