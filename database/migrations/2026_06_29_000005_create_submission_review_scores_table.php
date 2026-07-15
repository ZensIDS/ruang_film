<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionReviewScoresTable extends Migration
{
    public function up()
    {
        Schema::create('submission_review_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('review_rubric_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_title');
            $table->decimal('item_weight', 8, 2)->default(1);
            $table->decimal('score', 5, 2);
            $table->decimal('weighted_score', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['submission_review_id', 'review_rubric_item_id'], 'submission_review_score_item_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('submission_review_scores');
    }
}
