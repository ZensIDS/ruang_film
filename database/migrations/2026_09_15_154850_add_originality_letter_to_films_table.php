<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom untuk menyimpan file Surat Orisinalitas Karya.
     * Relasinya 1 film : 1 surat, jadi cukup ditambahkan sebagai kolom
     * pada tabel films (bukan tabel terpisah).
     */
    public function up(): void
    {
        Schema::table('films', function (Blueprint $table) {
            $table->string('originality_letter')->nullable()->after('other_2');
            $table->timestamp('originality_letter_uploaded_at')->nullable()->after('originality_letter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('films', function (Blueprint $table) {
            $table->dropColumn(['originality_letter', 'originality_letter_uploaded_at']);
        });
    }
};