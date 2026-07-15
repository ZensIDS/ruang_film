<?php

use App\Models\SubmissionSetting;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateSubmissionAndFilmTables extends Migration
{
    public function up()
    {
        Schema::table('submission_settings', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        DB::table('submission_settings')->orderBy('id')->get()->each(function ($setting) {
            $name = 'Periode ' . Carbon::parse($setting->open_at)->format('F Y');

            DB::table('submission_settings')
                ->where('id', $setting->id)
                ->update(['name' => $name]);
        });

        Schema::table('films', function (Blueprint $table) {
            $table->unsignedBigInteger('submission_setting_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('curation_status')->default('submitted');
            $table->text('curator_note')->nullable();
            $table->string('winner_rank')->nullable();
        });

        DB::table('films')->orderBy('id')->get()->each(function ($film) {
            $user = DB::table('users')->where('id', $film->user_id)->first();
            $setting = SubmissionSetting::resolveForDate($film->created_at);

            $curationStatus = 'submitted';
            $winnerRank = null;

            if (in_array((string) $film->status, ['4'], true)) {
                $curationStatus = 'approved';
            } elseif (in_array((string) $film->status, ['5'], true)) {
                $curationStatus = 'rejected';
            } elseif ((string) $film->status === '6') {
                $curationStatus = 'approved';
                $winnerRank = '1';
            }

            DB::table('films')->where('id', $film->id)->update([
                'submission_setting_id' => optional($setting)->id,
                'category_id' => optional($user)->category_id,
                'curation_status' => $curationStatus,
                'winner_rank' => $winnerRank,
            ]);
        });
    }

    public function down()
    {
        Schema::table('films', function (Blueprint $table) {
            $table->dropColumn([
                'submission_setting_id',
                'category_id',
                'curation_status',
                'curator_note',
                'winner_rank',
            ]);
        });

        Schema::table('submission_settings', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}
