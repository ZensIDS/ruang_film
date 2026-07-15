<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Film;
use App\Models\ReviewRubric;
use App\Models\SubmissionReview;
use App\Models\SubmissionSetting;
use App\Models\User;
use App\Models\UserDetail;
use Database\Seeders\RubrikPenilaianSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_submission_uses_active_period_and_category()
    {
        Storage::fake('public');

        $category = Category::factory()->create();
        $period = SubmissionSetting::factory()->create([
            'open_at' => now()->subDay(),
            'close_at' => now()->addDay(),
        ]);
        $participant = User::factory()->create([
            'role' => 'peserta',
            'category_id' => $category->id,
        ]);
        UserDetail::factory()->create(['user_id' => $participant->id]);

        $this->actingAs($participant)
            ->post(route('film.store'), [
                'name' => 'Film Uji',
                'duration' => 600,
                'tahun_produksi' => '2026',
                'subtitle' => 'Ya',
                'sinopsis' => 'Sinopsis film',
                'sutradara' => 'Sutradara',
                'produser' => 'Produser',
                'penulis' => 'Penulis',
                'kru' => UploadedFile::fake()->create('kru.pdf', 100),
                'poster' => UploadedFile::fake()->image('poster.jpg'),
                'gsm' => [
                    UploadedFile::fake()->image('gsm-1.jpg'),
                ],
                'trailer' => 'https://example.com/trailer',
                'film' => 'https://example.com/film',
                'other_2' => UploadedFile::fake()->create('attachment-2.pdf', 100),
            ])
            ->assertRedirect(route('film.index'));

        $film = Film::first();

        $this->assertSame($period->id, $film->submission_setting_id);
        $this->assertSame($category->id, $film->category_id);
        $this->assertSame(Film::CURATION_SUBMITTED, $film->curation_status);
        $this->assertNotNull($film->other_2);
    }

    public function test_admin_can_create_and_update_grouped_rubrics_manually()
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->post(route('categories.store'), [
                'name' => 'Kategori Rubrik',
                'slug' => 'kategori-rubrik',
                'description' => 'Deskripsi',
                'landing_summary' => 'Ringkasan',
                'detail_route' => '/rubrik',
                'sort_order' => 1,
                'is_active' => 1,
                'rubrics' => [
                    ReviewRubric::STAGE_CURATION => [
                        'present' => 1,
                        'groups' => [
                            [
                                'title' => 'NARASI & SKENARIO',
                                'weight' => 50,
                                'sort_order' => 0,
                                'items' => [
                                    [
                                        'title' => 'Orisinalitas dan Tema',
                                        'description' => 'Keunikan ide.',
                                        'weight' => 15,
                                        'sort_order' => 0,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    ReviewRubric::STAGE_JURY => [
                        'present' => 1,
                        'groups' => [
                            [
                                'title' => 'Rasa Film',
                                'weight' => null,
                                'sort_order' => 0,
                                'items' => [
                                    [
                                        'title' => 'After Taste',
                                        'description' => 'Jejak emosional.',
                                        'weight' => '',
                                        'sort_order' => 0,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('categories.index'));

        $category = Category::where('slug', 'kategori-rubrik')->firstOrFail();

        $this->assertDatabaseHas('review_rubrics', [
            'category_id' => $category->id,
            'stage' => ReviewRubric::STAGE_CURATION,
        ]);
        $this->assertDatabaseHas('review_rubric_items', [
            'title' => 'Orisinalitas dan Tema',
            'weight' => 15,
        ]);
        $this->assertDatabaseHas('review_rubric_items', [
            'title' => 'After Taste',
            'weight' => 1,
        ]);

        $this->actingAs($admin)
            ->put(route('categories.update', $category), [
                'name' => 'Kategori Rubrik Updated',
                'slug' => 'kategori-rubrik',
                'description' => 'Deskripsi',
                'landing_summary' => 'Ringkasan',
                'detail_route' => '/rubrik',
                'sort_order' => 1,
                'is_active' => 1,
                'rubrics' => [
                    ReviewRubric::STAGE_CURATION => [
                        'present' => 1,
                        'groups' => [
                            [
                                'title' => 'TEKNIK',
                                'weight' => 30,
                                'sort_order' => 0,
                                'items' => [
                                    [
                                        'title' => 'Sinematografi',
                                        'description' => 'Framing dan kontinuitas visual.',
                                        'weight' => 5,
                                        'sort_order' => 0,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('review_rubric_items', [
            'title' => 'Sinematografi',
            'weight' => 5,
        ]);
        $this->assertDatabaseMissing('review_rubric_items', [
            'title' => 'Orisinalitas dan Tema',
        ]);
    }

    public function test_admin_and_adminsub_have_separate_submission_and_review_pages()
    {
        foreach (['admin', 'adminsub'] as $role) {
            $user = User::factory()->role($role)->create();

            $this->actingAs($user)
                ->get(route('film.index'))
                ->assertOk()
                ->assertSee('Submission', false)
                ->assertDontSee('Submission & Review', false);

            $this->actingAs($user)
                ->get(route('review.index'))
                ->assertOk()
                ->assertSee('Review Submission', false)
                ->assertDontSee('Submission & Review', false);
        }
    }

    public function test_adminsub_submission_list_shows_edit_and_delete_actions()
    {
        $adminsub = User::factory()->role('adminsub')->create();
        $film = Film::factory()->create();

        $this->actingAs($adminsub)
            ->get(route('film.index'))
            ->assertOk()
            ->assertSee(route('film.edit', $film), false)
            ->assertSee('action="' . route('film.destroy', $film) . '"', false);
    }

    public function test_admin_submission_index_applies_period_category_and_status_filters()
    {
        $admin = User::factory()->role('admin')->create();
        $periodA = SubmissionSetting::factory()->create();
        $periodB = SubmissionSetting::factory()->create();
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $targetFilm = Film::factory()->create([
            'name' => 'Film Target Filter',
            'submission_setting_id' => $periodA->id,
            'category_id' => $categoryA->id,
            'curation_status' => Film::CURATION_APPROVED,
            'status' => Film::CURATION_APPROVED,
        ]);
        Film::factory()->create([
            'name' => 'Film Status Lain',
            'submission_setting_id' => $periodA->id,
            'category_id' => $categoryA->id,
            'curation_status' => Film::CURATION_SUBMITTED,
            'status' => Film::CURATION_SUBMITTED,
        ]);
        Film::factory()->create([
            'name' => 'Film Periode Lain',
            'submission_setting_id' => $periodB->id,
            'category_id' => $categoryA->id,
            'curation_status' => Film::CURATION_APPROVED,
            'status' => Film::CURATION_APPROVED,
        ]);
        Film::factory()->create([
            'name' => 'Film Kategori Lain',
            'submission_setting_id' => $periodA->id,
            'category_id' => $categoryB->id,
            'curation_status' => Film::CURATION_APPROVED,
            'status' => Film::CURATION_APPROVED,
        ]);

        $response = $this->actingAs($admin)->get(route('film.index', [
            'submission_setting_id' => $periodA->id,
            'category_id' => $categoryA->id,
            'curation_status' => Film::CURATION_APPROVED,
        ]));

        $response->assertOk()
            ->assertSee('Film Target Filter')
            ->assertDontSee('Film Status Lain')
            ->assertDontSee('Film Periode Lain')
            ->assertDontSee('Film Kategori Lain');
        $this->assertSame((string) $periodA->id, (string) $response->viewData('selectedSubmissionSettingId'));
        $this->assertSame((string) $categoryA->id, (string) $response->viewData('selectedCategoryId'));
        $this->assertSame(Film::CURATION_APPROVED, $response->viewData('selectedCurationStatus'));
    }

    public function test_admin_submission_index_orders_films_by_latest_upload()
    {
        $admin = User::factory()->role('admin')->create();
        $period = SubmissionSetting::factory()->create([
            'open_at' => now()->subDay(),
            'close_at' => now()->addDay(),
        ]);

        Film::factory()->create([
            'name' => 'Film Paling Lama',
            'submission_setting_id' => $period->id,
            'created_at' => now()->subDays(3),
        ]);
        Film::factory()->create([
            'name' => 'Film Tengah',
            'submission_setting_id' => $period->id,
            'created_at' => now()->subDay(),
        ]);
        Film::factory()->create([
            'name' => 'Film Terbaru',
            'submission_setting_id' => $period->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('film.index', [
            'submission_setting_id' => $period->id,
        ]));

        $response->assertOk();
        $this->assertSame([
            'Film Terbaru',
            'Film Tengah',
            'Film Paling Lama',
        ], $response->viewData('films')->pluck('name')->take(3)->values()->all());
    }

    public function test_admin_cannot_score_in_curation_or_jury_stage()
    {
        $admin = User::factory()->role('admin')->create();
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
        ]);
        $curationRubric = $this->createRubric($category, ReviewRubric::STAGE_CURATION, [
            ['title' => 'Narasi', 'weight' => 10],
        ]);
        $curationItem = $curationRubric->groups()->first()->items()->first();

        $this->actingAs($admin)
            ->get(route('review.score', [$film, ReviewRubric::STAGE_CURATION]))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($admin)
            ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_CURATION]), [
                'scores' => [$curationItem->id => 10],
            ])
            ->assertRedirect(route('dashboard'));

        $film->update(['curation_status' => Film::CURATION_APPROVED]);

        $juryRubric = $this->createRubric($category, ReviewRubric::STAGE_JURY, [
            ['title' => 'After Taste', 'weight' => 1],
        ]);
        $juryItem = $juryRubric->groups()->first()->items()->first();

        $this->actingAs($admin)
            ->get(route('review.score', [$film, ReviewRubric::STAGE_JURY]))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($admin)
            ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_JURY]), [
                'scores' => [$juryItem->id => 9],
            ])
            ->assertRedirect(route('dashboard'));
    }

    public function test_review_index_hides_stage_selector_and_keeps_role_based_stage_defaults()
    {
        $curatorResponse = $this->actingAs(User::factory()->role('kurator')->create())
            ->get(route('review.index', ['stage' => ReviewRubric::STAGE_JURY]));

        $curatorResponse->assertOk()
            ->assertDontSee('<label>Penilaian</label>', false);
        $this->assertSame(ReviewRubric::STAGE_CURATION, $curatorResponse->viewData('stage'));

        $juryResponse = $this->actingAs(User::factory()->role('juri')->create())
            ->get(route('review.index', ['stage' => ReviewRubric::STAGE_CURATION]));

        $juryResponse->assertOk()
            ->assertDontSee('<label>Penilaian</label>', false);
        $this->assertSame(ReviewRubric::STAGE_JURY, $juryResponse->viewData('stage'));

        $adminResponse = $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('review.index', ['stage' => ReviewRubric::STAGE_JURY]));

        $adminResponse->assertOk()
            ->assertDontSee('<label>Penilaian</label>', false);
        $this->assertSame(ReviewRubric::STAGE_JURY, $adminResponse->viewData('stage'));
    }

    public function test_admin_can_update_individual_film_status_and_clear_winner_rank()
    {
        $admin = User::factory()->role('admin')->create();
        $film = Film::factory()->create([
            'curation_status' => Film::CURATION_APPROVED,
            'winner_rank' => '1',
        ]);

        $this->actingAs($admin)
            ->from(route('review.index'))
            ->patch(route('review.status', $film), [
                'curation_status' => Film::CURATION_REJECTED,
            ])
            ->assertRedirect(route('review.index'));

        $film->refresh();

        $this->assertSame(Film::CURATION_REJECTED, $film->curation_status);
        $this->assertNull($film->winner_rank);
    }

    public function test_review_index_uses_effective_default_filters_for_curators()
    {
        $category = Category::factory()->create();
        $previousPeriod = SubmissionSetting::factory()->create([
            'open_at' => now()->subDays(10),
            'close_at' => now()->subDays(5),
        ]);
        $currentPeriod = SubmissionSetting::factory()->create([
            'open_at' => now()->subDay(),
            'close_at' => now()->addDay(),
        ]);
        $currentFilm = Film::factory()->create([
            'submission_setting_id' => $currentPeriod->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_VERIFIED,
            'status' => Film::CURATION_VERIFIED,
        ]);
        Film::factory()->create([
            'submission_setting_id' => $previousPeriod->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_VERIFIED,
            'status' => Film::CURATION_VERIFIED,
        ]);

        $response = $this->actingAs(User::factory()->role('kurator')->create())
            ->get(route('review.index'));

        $response->assertOk();

        $this->assertSame($currentPeriod->id, $response->viewData('selectedSubmissionSettingId'));
        $this->assertNull($response->viewData('selectedCategoryId'));
        $this->assertNull($response->viewData('selectedCurationStatus'));
        $this->assertSame(ReviewRubric::STAGE_CURATION, $response->viewData('stage'));
        $this->assertSame([$currentFilm->id], $response->viewData('films')->pluck('id')->all());
    }

    public function test_jury_review_index_only_lists_official_selection_films()
    {
        $juryCategory = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $period = SubmissionSetting::factory()->create([
            'open_at' => now()->subDay(),
            'close_at' => now()->addDay(),
        ]);
        $approvedFilm = Film::factory()->create([
            'name' => 'Backdoor',
            'submission_setting_id' => $period->id,
            'category_id' => $juryCategory->id,
            'curation_status' => Film::CURATION_APPROVED,
        ]);
        Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $juryCategory->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
        ]);
        Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $otherCategory->id,
            'curation_status' => Film::CURATION_APPROVED,
        ]);

        $jury = User::factory()->role('juri')->create([
            'category_id' => $juryCategory->id,
        ]);

        $response = $this->actingAs($jury)
            ->get(route('review.index'));

        $response->assertOk();

        $this->assertSame((string) $juryCategory->id, (string) $response->viewData('selectedCategoryId'));
        $this->assertSame(Film::CURATION_APPROVED, $response->viewData('selectedCurationStatus'));
        $this->assertSame(ReviewRubric::STAGE_JURY, $response->viewData('stage'));
        $this->assertSame([$approvedFilm->id], $response->viewData('films')->pluck('id')->all());
    }

    public function test_review_index_empty_state_does_not_render_a_colspan_row()
    {
        $response = $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('review.index'));

        $response->assertOk()
            ->assertDontSee('Belum ada submission.</td>', false)
            ->assertDontSee('colspan="', false);
    }

    public function test_raw_rubric_scoring_stores_item_snapshots_and_total()
    {
        $category = Category::factory()->create();
        $period = SubmissionSetting::factory()->create();
        $film = Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
        ]);
        $rubric = $this->createRubric($category, ReviewRubric::STAGE_CURATION, [
            ['title' => 'Orisinalitas', 'weight' => 15],
            ['title' => 'Struktur Cerita', 'weight' => 10],
        ]);
        $items = $rubric->groups()->first()->items;
        $curator = User::factory()->role('kurator')->create();

        $this->actingAs($curator)
            ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_CURATION]), [
                'scores' => [
                    $items[0]->id => 10,
                    $items[1]->id => 8,
                ],
                'note' => 'Layak lanjut',
            ])
            ->assertRedirect(route('review.index', [
                'submission_setting_id' => $film->submission_setting_id,
                'category_id' => $film->category_id,
                'stage' => ReviewRubric::STAGE_CURATION,
            ]));

        $review = SubmissionReview::with('scores')->firstOrFail();

        $this->assertSame(230.0, (float) $review->total_score);
        $this->assertSame('Layak lanjut', $review->note);
        $this->assertCount(2, $review->scores);
        $this->assertSame(150.0, (float) $review->scores->firstWhere('review_rubric_item_id', $items[0]->id)->weighted_score);
        $this->assertSame(10.0, (float) $review->scores->firstWhere('review_rubric_item_id', $items[1]->id)->item_weight);
    }

    public function test_multiple_curators_produce_average_curation_ranking()
    {
        $category = Category::factory()->create();
        $period = SubmissionSetting::factory()->create();
        $filmA = Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
        ]);
        $filmB = Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
        ]);
        $rubric = $this->createRubric($category, ReviewRubric::STAGE_CURATION, [
            ['title' => 'Total Kesan', 'weight' => 10],
        ]);
        $item = $rubric->groups()->first()->items()->first();
        $curatorA = User::factory()->role('kurator')->create();
        $curatorB = User::factory()->role('kurator')->create();

        $this->actingAs($curatorA)->patch(route('review.score.update', [$filmA, ReviewRubric::STAGE_CURATION]), [
            'scores' => [$item->id => 10],
        ]);
        $this->actingAs($curatorB)->patch(route('review.score.update', [$filmA, ReviewRubric::STAGE_CURATION]), [
            'scores' => [$item->id => 8],
        ]);
        $this->actingAs($curatorA)->patch(route('review.score.update', [$filmB, ReviewRubric::STAGE_CURATION]), [
            'scores' => [$item->id => 5],
        ]);

        $response = $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('review.index', [
                'submission_setting_id' => $period->id,
                'category_id' => $category->id,
                'curation_status' => Film::CURATION_UNDER_REVIEW,
                'stage' => ReviewRubric::STAGE_CURATION,
            ]));

        $response->assertOk();

        $films = $response->viewData('films');

        $this->assertSame([$filmA->id, $filmB->id], $films->pluck('id')->all());
        $this->assertSame(90.0, (float) $films->first()->curation_average_score);
        $this->assertSame(2, $films->first()->curation_review_count);
    }

    public function test_review_index_displays_spreadsheet_columns_and_reviewer_totals()
    {
        $category = Category::factory()->create();
        $period = SubmissionSetting::factory()->create();
        $film = Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
        ]);
        $rubric = $this->createRubric($category, ReviewRubric::STAGE_CURATION, [
            ['title' => 'Total Kesan', 'weight' => 10],
        ]);
        $item = $rubric->groups()->first()->items()->first();
        $curator = User::factory()->role('kurator')->create(['name' => 'Kurator Satu']);

        $this->actingAs($curator)->patch(route('review.score.update', [$film, ReviewRubric::STAGE_CURATION]), [
            'scores' => [$item->id => 10],
            'note' => 'Kuat',
        ]);

        $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('review.index', [
                'submission_setting_id' => $period->id,
                'category_id' => $category->id,
                'stage' => ReviewRubric::STAGE_CURATION,
            ]))
            ->assertOk()
            ->assertSee('Nama Tim/Komunitas Produksi')
            ->assertSee('Total Kesan')
            ->assertSee('Kurator Satu')
            ->assertSee('100.00')
            ->assertSee('Kuat');
    }

    public function test_film_show_displays_reviewer_breakdown()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
        ]);
        $rubric = $this->createRubric($category, ReviewRubric::STAGE_CURATION, [
            ['title' => 'Narasi', 'weight' => 10],
        ]);
        $item = $rubric->groups()->first()->items()->first();
        $curator = User::factory()->role('kurator')->create(['name' => 'Kurator Detail']);

        $this->actingAs($curator)->patch(route('review.score.update', [$film, ReviewRubric::STAGE_CURATION]), [
            'scores' => [$item->id => 9],
            'note' => 'Detail catatan',
        ]);

        $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('film.show', $film))
            ->assertOk()
            ->assertSee('Rekap Penilaian')
            ->assertSee('Kurator Detail')
            ->assertSee('Narasi')
            ->assertSee('90.00')
            ->assertSee('Detail catatan');
    }

    public function test_curators_cannot_score_films_that_are_already_in_determination()
    {
        $category = Category::factory()->create();
        $closedPeriod = SubmissionSetting::factory()->create([
            'open_at' => now()->subDays(10),
            'close_at' => now()->subDay(),
        ]);
        $film = Film::factory()->create([
            'submission_setting_id' => $closedPeriod->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_DETERMINATION,
        ]);
        $rubric = $this->createRubric($category, ReviewRubric::STAGE_CURATION, [
            ['title' => 'Narasi', 'weight' => 10],
        ]);
        $item = $rubric->groups()->first()->items()->first();
        $curator = User::factory()->role('kurator')->create();

        $this->actingAs($curator)
            ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_CURATION]), [
                'scores' => [$item->id => 10],
            ])
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('submission_reviews', 0);
    }

    public function test_closed_submission_period_does_not_override_existing_curation_status()
    {
        $category = Category::factory()->create();
        $closedPeriod = SubmissionSetting::factory()->create([
            'open_at' => now()->subDays(10),
            'close_at' => now()->subDay(),
        ]);
        $film = Film::factory()->create([
            'submission_setting_id' => $closedPeriod->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
            'status' => Film::CURATION_UNDER_REVIEW,
        ]);

        $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('review.index', [
                'submission_setting_id' => $closedPeriod->id,
                'category_id' => $category->id,
            ]))
            ->assertOk();

        $film->refresh();

        $this->assertSame(Film::CURATION_UNDER_REVIEW, $film->curation_status);
        $this->assertSame(Film::CURATION_UNDER_REVIEW, $film->status);
    }

    public function test_closed_submission_period_promotes_verified_film_to_under_review()
    {
        $category = Category::factory()->create();
        $closedPeriod = SubmissionSetting::factory()->create([
            'open_at' => now()->subDays(10),
            'close_at' => now()->subDay(),
        ]);
        $film = Film::factory()->create([
            'submission_setting_id' => $closedPeriod->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_VERIFIED,
            'status' => Film::CURATION_VERIFIED,
        ]);

        $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('review.index', [
                'submission_setting_id' => $closedPeriod->id,
                'category_id' => $category->id,
            ]))
            ->assertOk();

        $film->refresh();

        $this->assertSame(Film::CURATION_UNDER_REVIEW, $film->curation_status);
        $this->assertSame(Film::CURATION_UNDER_REVIEW, $film->status);
    }

    public function test_closed_submission_period_keeps_submitted_film_as_submitted()
    {
        $category = Category::factory()->create();
        $closedPeriod = SubmissionSetting::factory()->create([
            'open_at' => now()->subDays(10),
            'close_at' => now()->subDay(),
        ]);
        $film = Film::factory()->create([
            'submission_setting_id' => $closedPeriod->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_SUBMITTED,
            'status' => Film::CURATION_SUBMITTED,
        ]);

        $this->actingAs(User::factory()->role('admin')->create())
            ->get(route('review.index', [
                'submission_setting_id' => $closedPeriod->id,
                'category_id' => $category->id,
            ]))
            ->assertOk();

        $film->refresh();

        $this->assertSame(Film::CURATION_SUBMITTED, $film->curation_status);
        $this->assertSame(Film::CURATION_SUBMITTED, $film->status);
    }

    public function test_film_show_timeline_uses_submitted_status_when_film_is_newly_sent()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_SUBMITTED,
            'status' => Film::CURATION_SUBMITTED,
        ]);

        $this->actingAs($film->user)
            ->get(route('film.show', $film))
            ->assertOk()
            ->assertSeeTextInOrder(['Submitted', 'Saat ini'])
            ->assertDontSeeText('Verified Saat ini')
<<<<<<< HEAD
            ->assertDontSeeText('Dalam Kurasi Saat ini');
=======
            ->assertDontSeeText('Under Review Saat ini');
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
    }

    public function test_film_show_timeline_uses_verified_status_after_admin_verification()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_VERIFIED,
            'status' => Film::CURATION_VERIFIED,
        ]);

        $this->actingAs($film->user)
            ->get(route('film.show', $film))
            ->assertOk()
            ->assertSeeTextInOrder(['Verified', 'Saat ini'])
<<<<<<< HEAD
            ->assertDontSeeText('Dalam Kurasi Saat ini');
=======
            ->assertDontSeeText('Under Review Saat ini');
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
    }

    public function test_film_show_timeline_uses_the_same_under_review_status_seen_by_participants()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
            'status' => Film::CURATION_UNDER_REVIEW,
        ]);

        $this->actingAs($film->user)
            ->get(route('film.show', $film))
            ->assertOk()
<<<<<<< HEAD
            ->assertSeeTextInOrder(['Dalam Kurasi', 'Saat ini'])
=======
            ->assertSeeTextInOrder(['Under Review', 'Saat ini'])
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
            ->assertDontSeeText('Dalam Penentuan Saat ini');
    }

    public function test_film_show_timeline_uses_official_selection_when_film_is_approved()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_APPROVED,
            'status' => Film::CURATION_APPROVED,
        ]);

        $this->actingAs($film->user)
            ->get(route('film.show', $film))
            ->assertOk()
            ->assertSeeTextInOrder(['Official Selection', 'Saat ini'])
            ->assertDontSeeText('Dalam Penentuan Saat ini');
    }

    public function test_participant_views_shortlist_status_as_dalam_kurasi()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_DETERMINATION,
            'status' => Film::CURATION_DETERMINATION,
        ]);

        $this->actingAs($film->user)
            ->get(route('film.show', $film))
            ->assertOk()
<<<<<<< HEAD
            ->assertSeeTextInOrder(['Dalam Kurasi', 'Saat ini'])
=======
            ->assertSeeTextInOrder(['Under Review', 'Saat ini'])
>>>>>>> 9e8c2069fe474883803df35494add3af52868881
            ->assertDontSeeText('Shortlist Saat ini');
    }

    public function test_admin_review_index_displays_shortlist_and_tidak_lolos_labels()
    {
        $admin = User::factory()->role('admin')->create();
        $category = Category::factory()->create();
        $period = SubmissionSetting::factory()->create();

        Film::factory()->create([
            'name' => 'Film Shortlist',
            'submission_setting_id' => $period->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_DETERMINATION,
            'status' => Film::CURATION_DETERMINATION,
        ]);
        Film::factory()->create([
            'name' => 'Film Verified',
            'submission_setting_id' => $period->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_VERIFIED,
            'status' => Film::CURATION_VERIFIED,
        ]);
        Film::factory()->create([
            'name' => 'Film Tidak Lolos',
            'submission_setting_id' => $period->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_REJECTED,
            'status' => Film::CURATION_REJECTED,
        ]);

        $this->actingAs($admin)
            ->get(route('review.index', [
                'submission_setting_id' => $period->id,
                'category_id' => $category->id,
            ]))
            ->assertOk()
            ->assertSee('Verified')
            ->assertSee('Shortlist')
            ->assertSee('Tidak Lolos');
    }

    public function test_official_selection_bulk_action_approves_checked_films_and_rejects_unchecked_films()
    {
        $admin = User::factory()->role('admin')->create();
        $category = Category::factory()->create();
        $period = SubmissionSetting::factory()->create([
            'open_at' => now()->subDays(10),
            'close_at' => now()->subDay(),
        ]);
        $selectedFilm = Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_DETERMINATION,
            'status' => Film::CURATION_DETERMINATION,
        ]);
        $rejectedFilm = Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_DETERMINATION,
            'status' => Film::CURATION_DETERMINATION,
        ]);

        $this->actingAs($admin)
            ->post(route('review.official-selection'), [
                'submission_setting_id' => $period->id,
                'category_id' => $category->id,
                'film_ids' => [$selectedFilm->id],
            ])
            ->assertRedirect();

        $this->assertSame(Film::CURATION_APPROVED, $selectedFilm->fresh()->curation_status);
        $this->assertSame(Film::CURATION_REJECTED, $rejectedFilm->fresh()->curation_status);
    }

    public function test_only_official_selection_films_can_be_scored_by_jury()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
        ]);
        $rubric = $this->createRubric($category, ReviewRubric::STAGE_JURY, [
            ['title' => 'After Taste', 'weight' => 1],
        ]);
        $item = $rubric->groups()->first()->items()->first();
        $jury = User::factory()->role('juri')->create([
            'category_id' => $category->id,
        ]);

        $this->actingAs($jury)
            ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_JURY]), [
                'scores' => [$item->id => 9],
            ])
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('submission_reviews', 0);

        $film->update(['curation_status' => Film::CURATION_APPROVED]);

        $this->actingAs($jury)
            ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_JURY]), [
                'scores' => [$item->id => 9],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('submission_reviews', [
            'film_id' => $film->id,
            'reviewer_id' => $jury->id,
            'stage' => ReviewRubric::STAGE_JURY,
        ]);
    }

    public function test_jury_cannot_score_official_selection_from_another_category()
    {
        $juryCategory = Category::factory()->create();
        $filmCategory = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $filmCategory->id,
            'curation_status' => Film::CURATION_APPROVED,
            'status' => Film::CURATION_APPROVED,
        ]);
        $rubric = $this->createRubric($filmCategory, ReviewRubric::STAGE_JURY, [
            ['title' => 'After Taste', 'weight' => 1],
        ]);
        $item = $rubric->groups()->first()->items()->first();
        $jury = User::factory()->role('juri')->create([
            'category_id' => $juryCategory->id,
        ]);

        $this->actingAs($jury)
            ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_JURY]), [
                'scores' => [$item->id => 9],
            ])
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('submission_reviews', 0);
    }

    public function test_curators_cannot_score_submitted_films()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_SUBMITTED,
            'status' => Film::CURATION_SUBMITTED,
        ]);
        $rubric = $this->createRubric($category, ReviewRubric::STAGE_CURATION, [
            ['title' => 'Narasi', 'weight' => 10],
        ]);
        $item = $rubric->groups()->first()->items()->first();
        $curator = User::factory()->role('kurator')->create();

        $this->actingAs($curator)
            ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_CURATION]), [
                'scores' => [$item->id => 9],
            ])
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('submission_reviews', 0);
    }

    public function test_curators_can_score_verified_films()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_VERIFIED,
            'status' => Film::CURATION_VERIFIED,
        ]);
        $rubric = $this->createRubric($category, ReviewRubric::STAGE_CURATION, [
            ['title' => 'Narasi', 'weight' => 10],
        ]);
        $item = $rubric->groups()->first()->items()->first();
        $curator = User::factory()->role('kurator')->create();

        $this->actingAs($curator)
            ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_CURATION]), [
                'scores' => [$item->id => 9],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('submission_reviews', [
            'film_id' => $film->id,
            'reviewer_id' => $curator->id,
            'stage' => ReviewRubric::STAGE_CURATION,
        ]);
    }

    public function test_curator_scores_must_be_whole_numbers()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
        ]);
        $rubric = $this->createRubric($category, ReviewRubric::STAGE_CURATION, [
            ['title' => 'Narasi', 'weight' => 10],
        ]);
        $item = $rubric->groups()->first()->items()->first();
        $curator = User::factory()->role('kurator')->create();

        $this->actingAs($curator)
            ->from(route('review.score', [$film, ReviewRubric::STAGE_CURATION]))
            ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_CURATION]), [
                'scores' => [$item->id => '9.5'],
            ])
            ->assertRedirect(route('review.score', [$film, ReviewRubric::STAGE_CURATION]))
            ->assertSessionHasErrors('scores.' . $item->id);

        $this->assertDatabaseCount('submission_reviews', 0);
    }

    public function test_curator_scores_must_stay_between_one_and_ten()
    {
        $category = Category::factory()->create();
        $film = Film::factory()->create([
            'category_id' => $category->id,
            'curation_status' => Film::CURATION_UNDER_REVIEW,
        ]);
        $rubric = $this->createRubric($category, ReviewRubric::STAGE_CURATION, [
            ['title' => 'Narasi', 'weight' => 10],
        ]);
        $item = $rubric->groups()->first()->items()->first();
        $curator = User::factory()->role('kurator')->create();

        collect(['0', '-1', '11'])->each(function ($invalidScore) use ($curator, $film, $item) {
            $this->actingAs($curator)
                ->from(route('review.score', [$film, ReviewRubric::STAGE_CURATION]))
                ->patch(route('review.score.update', [$film, ReviewRubric::STAGE_CURATION]), [
                    'scores' => [$item->id => $invalidScore],
                ])
                ->assertRedirect(route('review.score', [$film, ReviewRubric::STAGE_CURATION]))
                ->assertSessionHasErrors('scores.' . $item->id);
        });

        $this->assertDatabaseCount('submission_reviews', 0);
    }

    public function test_winner_rank_is_unique_per_submission_period_and_category()
    {
        $admin = User::factory()->role('admin')->create();
        $period = SubmissionSetting::factory()->create();
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();
        $filmA = Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $categoryA->id,
            'curation_status' => Film::CURATION_APPROVED,
        ]);
        $filmB = Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $categoryA->id,
            'curation_status' => Film::CURATION_APPROVED,
        ]);
        $filmC = Film::factory()->create([
            'submission_setting_id' => $period->id,
            'category_id' => $categoryB->id,
            'curation_status' => Film::CURATION_APPROVED,
        ]);

        $this->actingAs($admin)
            ->patch(route('review.winner-rank', $filmA), ['winner_rank' => 1])
            ->assertRedirect();
        $this->actingAs($admin)
            ->patch(route('review.winner-rank', $filmC), ['winner_rank' => 1])
            ->assertRedirect();

        $response = $this->actingAs($admin)
            ->from(route('review.index'))
            ->patch(route('review.winner-rank', $filmB), ['winner_rank' => 1]);

        $response->assertSessionHasErrors('winner_rank');
        $this->assertSame('1', $filmA->fresh()->winner_rank);
        $this->assertSame('1', $filmC->fresh()->winner_rank);
        $this->assertNull($filmB->fresh()->winner_rank);
    }

    public function test_rubrik_penilaian_seeder_populates_existing_categories_from_xlsx_reference()
    {
        collect([
            'umum-nasional' => 'Umum Nasional',
            'pelajar-jawa-timur' => 'Pelajar Se - Jawa Timur',
            'organisasi-komunitas-pacitan' => 'Organisasi & Komunitas Lokal Pacitan',
            'pelajar-sd-smp-pacitan' => 'Pelajar SD - SMP Se-Pacitan',
        ])->each(function ($name, $slug) {
            Category::factory()->create([
                'name' => $name,
                'slug' => $slug,
            ]);
        });

        $this->seed(RubrikPenilaianSeeder::class);

        $umum = Category::where('slug', 'umum-nasional')->firstOrFail();
        $pelajar = Category::where('slug', 'pelajar-jawa-timur')->firstOrFail();

        $this->assertSame(11, $umum->activeRubric(ReviewRubric::STAGE_CURATION)->groups->flatMap(function ($group) {
            return $group->items;
        })->count());
        $this->assertSame(12, $pelajar->activeRubric(ReviewRubric::STAGE_CURATION)->groups->flatMap(function ($group) {
            return $group->items;
        })->count());
        $this->assertSame(6, $umum->activeRubric(ReviewRubric::STAGE_JURY)->groups->flatMap(function ($group) {
            return $group->items;
        })->count());
        $this->assertDatabaseHas('review_rubric_items', [
            'title' => 'Rasa Takut Yang Bermakna',
            'weight' => 1,
        ]);
        $this->assertDatabaseHas('review_rubric_items', [
            'title' => 'Pesan Moral',
            'weight' => 15,
        ]);
    }

    private function createRubric(Category $category, $stage, array $items)
    {
        $rubric = ReviewRubric::create([
            'category_id' => $category->id,
            'stage' => $stage,
            'name' => $category->name . ' - ' . $stage,
            'is_active' => true,
        ]);

        $group = $rubric->groups()->create([
            'title' => 'Aspek Utama',
            'weight' => null,
            'sort_order' => 0,
        ]);

        foreach ($items as $index => $item) {
            $group->items()->create([
                'title' => $item['title'],
                'description' => $item['description'] ?? null,
                'weight' => $item['weight'] ?? 1,
                'sort_order' => $index,
            ]);
        }

        return $rubric->fresh('groups.items');
    }
}
