<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeFilmStatusesAndWinnerRanks extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('films')) {
            return;
        }

        DB::table('films')->orderBy('id')->get()->each(function ($film) {
            $normalizedStatus = $this->normalizeStatus($film->curation_status, $film->status);
            $normalizedWinnerRank = $this->normalizeWinnerRank($film->winner_rank);

            if ($normalizedStatus !== 'approved') {
                $normalizedWinnerRank = null;
            }

            DB::table('films')
                ->where('id', $film->id)
                ->update([
                    'status' => $normalizedStatus,
                    'curation_status' => $normalizedStatus,
                    'winner_rank' => $normalizedWinnerRank,
                ]);
        });

        $this->updateDefaults('submitted');
    }

    public function down()
    {
        if (!Schema::hasTable('films')) {
            return;
        }

        DB::table('films')->orderBy('id')->get()->each(function ($film) {
            $status = $film->curation_status === 'determination'
                ? 'pending'
                : ($film->curation_status ?: 'submitted');

            DB::table('films')
                ->where('id', $film->id)
                ->update([
                    'status' => $status,
                    'curation_status' => $status,
                    'winner_rank' => $film->winner_rank ? 'Juara ' . $film->winner_rank : null,
                ]);
        });

        $this->updateDefaults('pending');
    }

    protected function normalizeStatus($curationStatus, $status)
    {
        $candidates = [$curationStatus, $status];

        foreach ($candidates as $candidate) {
            $normalizedCandidate = $this->normalizeStatusCandidate($candidate);

            if ($normalizedCandidate !== null) {
                return $normalizedCandidate;
            }
        }

        return 'submitted';
    }

    protected function normalizeStatusCandidate($value)
    {
        $value = (string) $value;

        if ($value === '') {
            return null;
        }

        if (in_array($value, ['submitted', 'under_review', 'determination', 'approved', 'rejected'], true)) {
            return $value;
        }

        if ($value === 'pending') {
            return 'determination';
        }

        if (in_array($value, ['1', '2', '3'], true)) {
            return 'submitted';
        }

        if ($value === '4') {
            return 'approved';
        }

        if ($value === '5') {
            return 'rejected';
        }

        if ($value === '6' || $value === 'winner') {
            return 'approved';
        }

        return null;
    }

    protected function normalizeWinnerRank($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $numericValue = (int) $value;

            return $numericValue > 0 ? (string) $numericValue : null;
        }

        preg_match('/(\d+)/', (string) $value, $matches);
        $numericValue = (int) ($matches[1] ?? 0);

        return $numericValue > 0 ? (string) $numericValue : null;
    }

    protected function updateDefaults($defaultStatus)
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE films MODIFY status VARCHAR(255) NOT NULL DEFAULT '" . $defaultStatus . "'");
            DB::statement("ALTER TABLE films MODIFY curation_status VARCHAR(255) NOT NULL DEFAULT '" . $defaultStatus . "'");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE films ALTER COLUMN status SET DEFAULT '" . $defaultStatus . "'");
            DB::statement("ALTER TABLE films ALTER COLUMN curation_status SET DEFAULT '" . $defaultStatus . "'");
        }
    }
}
