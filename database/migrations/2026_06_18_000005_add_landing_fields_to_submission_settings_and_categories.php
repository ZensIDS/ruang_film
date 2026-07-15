<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLandingFieldsToSubmissionSettingsAndCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submission_settings', function (Blueprint $table) {
            $table->string('hero_title')->nullable()->after('name');
            $table->text('hero_description')->nullable()->after('hero_title');
            $table->string('hero_image')->nullable()->after('hero_description');
            $table->string('about_title')->nullable()->after('hero_image');
            $table->text('about_description')->nullable()->after('about_title');
            $table->text('about_description_secondary')->nullable()->after('about_description');
            $table->string('about_image')->nullable()->after('about_description_secondary');
            $table->string('hashtag')->nullable()->after('about_image');
            $table->string('theme_title')->nullable()->after('hashtag');
            $table->string('theme_name')->nullable()->after('theme_title');
            $table->text('theme_quote')->nullable()->after('theme_name');
            $table->longText('theme_description')->nullable()->after('theme_quote');
            $table->string('theme_image')->nullable()->after('theme_description');
            $table->json('festival_board')->nullable()->after('theme_image');
            $table->string('last_year_title')->nullable()->after('festival_board');
            $table->text('last_year_description')->nullable()->after('last_year_title');
            $table->string('last_year_catalog_label')->nullable()->after('last_year_description');
            $table->string('last_year_catalog_url')->nullable()->after('last_year_catalog_label');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->text('landing_summary')->nullable()->after('description');
            $table->string('image')->nullable()->after('landing_summary');
            $table->string('detail_route')->nullable()->after('image');
            $table->unsignedInteger('sort_order')->default(0)->after('detail_route');
            $table->boolean('is_active')->default(true)->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'description',
                'landing_summary',
                'image',
                'detail_route',
                'sort_order',
                'is_active',
            ]);
        });

        Schema::table('submission_settings', function (Blueprint $table) {
            $table->dropColumn([
                'hero_title',
                'hero_description',
                'hero_image',
                'about_title',
                'about_description',
                'about_description_secondary',
                'about_image',
                'hashtag',
                'theme_title',
                'theme_name',
                'theme_quote',
                'theme_description',
                'theme_image',
                'festival_board',
                'last_year_title',
                'last_year_description',
                'last_year_catalog_label',
                'last_year_catalog_url',
            ]);
        });
    }
}
