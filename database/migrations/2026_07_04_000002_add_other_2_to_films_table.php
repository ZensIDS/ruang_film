<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOther2ToFilmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('films', function (Blueprint $table) {
                $table->string('other_2')->nullable()->after('other_1');
            });

            return;
        }

        Schema::table('films', function (Blueprint $table) {
            $table->string('other_2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('films', function (Blueprint $table) {
            $table->dropColumn('other_2');
        });
    }
}
