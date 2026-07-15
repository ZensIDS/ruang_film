<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubmissionReviewsTable extends Migration
{
    public function up()
    {
        Schema::create('submission_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('film_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('review_rubric_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage');
            $table->decimal('total_score', 10, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['film_id', 'reviewer_id', 'stage']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('submission_reviews');
    }
}
