<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJuryScoresTable extends Migration
{
    public function up()
    {
        Schema::create('jury_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('film_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jury_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['film_id', 'jury_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('jury_scores');
    }
}
