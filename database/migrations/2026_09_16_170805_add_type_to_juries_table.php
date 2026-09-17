<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTypeToJuriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tambah kolom 'type' jika belum ada
        if (!Schema::hasColumn('juries', 'type')) {
            Schema::table('juries', function (Blueprint $table) {
                $table->string('type', 20)->default('juri')->after('id');
            });
        }

        // 2. Lepas Foreign Key menggunakan Schema Builder Laravel
        try {
            Schema::table('juries', function (Blueprint $table) {
                $table->dropForeign('juries_category_id_foreign');
            });
        } catch (\Throwable $e) {
            // Jika sudah ter-drop, lanjutkan
        }

        // 3. Ubah category_id menjadi NULLable via DB Statement
        DB::statement('ALTER TABLE juries MODIFY category_id BIGINT UNSIGNED NULL');

        // 4. Pasang kembali Foreign Key
        Schema::table('juries', function (Blueprint $table) {
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            Schema::table('juries', function (Blueprint $table) {
                $table->dropForeign('juries_category_id_foreign');
            });
        } catch (\Throwable $e) {
            // Ignore jika FK tidak ditemukan
        }

        // Isi category_id yang NULL sebelum dikembalikan ke NOT NULL
        DB::table('juries')->whereNull('category_id')->update([
            'category_id' => DB::table('categories')->min('id'),
        ]);

        DB::statement('ALTER TABLE juries MODIFY category_id BIGINT UNSIGNED NOT NULL');

        Schema::table('juries', function (Blueprint $table) {
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->cascadeOnDelete();

            if (Schema::hasColumn('juries', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
}