<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewRubricGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('review_rubric_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_rubric_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->decimal('weight', 8, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_rubric_groups');
    }
}
