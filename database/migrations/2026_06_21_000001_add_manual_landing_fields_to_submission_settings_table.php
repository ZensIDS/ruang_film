<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManualLandingFieldsToSubmissionSettingsTable extends Migration
{
    public function up()
    {
        Schema::table('submission_settings', function (Blueprint $table) {
            $table->json('last_year_featured_film_ids')->nullable()->after('last_year_catalog_url');
            $table->string('last_year_catalog_file')->nullable()->after('last_year_featured_film_ids');
            $table->unsignedInteger('last_year_stat_film_submitted')->nullable()->after('last_year_catalog_file');
            $table->unsignedInteger('last_year_stat_special_films')->nullable()->after('last_year_stat_film_submitted');
            $table->unsignedInteger('last_year_stat_audience')->nullable()->after('last_year_stat_special_films');
            $table->unsignedInteger('last_year_stat_participants')->nullable()->after('last_year_stat_audience');
            $table->json('timeline_items')->nullable()->after('last_year_stat_participants');
        });
    }

    public function down()
    {
        Schema::table('submission_settings', function (Blueprint $table) {
            $table->dropColumn([
                'last_year_featured_film_ids',
                'last_year_catalog_file',
                'last_year_stat_film_submitted',
                'last_year_stat_special_films',
                'last_year_stat_audience',
                'last_year_stat_participants',
                'timeline_items',
            ]);
        });
    }
}
