<?php

namespace Database\Factories;

use App\Models\ReviewRubricItem;
use App\Models\SubmissionReview;
use App\Models\SubmissionReviewScore;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionReviewScoreFactory extends Factory
{
    protected $model = SubmissionReviewScore::class;

    public function definition()
    {
        return [
            'submission_review_id' => SubmissionReview::factory(),
            'review_rubric_item_id' => ReviewRubricItem::factory(),
            'item_title' => $this->faker->words(3, true),
            'item_weight' => 1,
            'score' => $this->faker->numberBetween(1, 10),
            'weighted_score' => 0,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (SubmissionReviewScore $reviewScore) {
            $rubricItem = $reviewScore->rubricItem;

            if (!$rubricItem) {
                return;
            }

            $weight = (float) $rubricItem->weight;
            $score = (float) $reviewScore->score;

            $reviewScore->update([
                'item_title' => $rubricItem->title,
                'item_weight' => $weight,
                'weighted_score' => round($score * $weight, 2),
            ]);
        });
    }
}
