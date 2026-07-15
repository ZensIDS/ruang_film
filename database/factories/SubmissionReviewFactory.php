<?php

namespace Database\Factories;

use App\Models\Film;
use App\Models\ReviewRubric;
use App\Models\SubmissionReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionReviewFactory extends Factory
{
    protected $model = SubmissionReview::class;

    public function definition()
    {
        return [
            'film_id' => Film::factory(),
            'reviewer_id' => User::factory()->role('kurator'),
            'review_rubric_id' => ReviewRubric::factory()->curation(),
            'stage' => ReviewRubric::STAGE_CURATION,
            'total_score' => $this->faker->randomFloat(2, 60, 100),
            'note' => $this->faker->sentence(),
            'submitted_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (SubmissionReview $review) {
            $film = $review->film;

            if (!$film) {
                return;
            }

            $rubric = ReviewRubric::firstOrCreate(
                [
                    'category_id' => $film->category_id,
                    'stage' => $review->stage,
                ],
                [
                    'name' => optional($film->category)->name . ' - ' . (ReviewRubric::stageLabels()[$review->stage] ?? ucfirst($review->stage)),
                    'is_active' => true,
                ]
            );

            if ((int) $review->review_rubric_id !== (int) $rubric->id) {
                $review->update(['review_rubric_id' => $rubric->id]);
            }
        });
    }

    public function jury()
    {
        return $this->state(function () {
            return [
                'reviewer_id' => User::factory()->role('juri'),
                'review_rubric_id' => ReviewRubric::factory()->jury(),
                'stage' => ReviewRubric::STAGE_JURY,
            ];
        });
    }
}
