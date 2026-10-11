<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestructureBioskopsToSchedules extends Migration
{
    /**
     * Dari 1 baris = 1 tanggal + sesi, menjadi 1 baris = 1 bioskop
     * dengan jadwal banyak tanggal (masing-masing punya sesi sendiri).
     *
     * schedules: [{date: "2026-10-11", sessions: [{label, time, status}, ...]}, ...]
     */
    public function up()
    {
        // Struktur lama tidak bisa dikonversi otomatis, data bioskop dikosongkan.
        DB::table('bioskops')->truncate();

        Schema::table('bioskops', function (Blueprint $table) {
            if (Schema::hasColumn('bioskops', 'show_date')) {
                $table->dropColumn('show_date');
            }
            if (Schema::hasColumn('bioskops', 'sessions')) {
                $table->dropColumn('sessions');
            }
            $table->json('schedules')->nullable()->after('address');
        });
    }

    public function down()
    {
        DB::table('bioskops')->truncate();

        Schema::table('bioskops', function (Blueprint $table) {
            $table->dropColumn('schedules');
            $table->date('show_date')->nullable()->after('address');
            $table->json('sessions')->nullable()->after('show_date');
        });
    }
}