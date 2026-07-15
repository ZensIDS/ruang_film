<?php

namespace Tests\Feature;

use App\Models\Merchandise;
use App\Models\MerchandiseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchandiseCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_database_driven_merchandise()
    {
        $category = MerchandiseCategory::factory()->create([
            'name' => 'Apparel',
            'slug' => 'apparel',
        ]);

        Merchandise::factory()->create([
            'merchandise_category_id' => $category->id,
            'name' => 'Database Shirt',
            'qty_stock' => 5,
            'weight' => 200,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Database Shirt')
            ->assertSee('Apparel');
    }

    public function test_merchandise_page_filters_and_paginates_results()
    {
        $apparel = MerchandiseCategory::factory()->create([
            'name' => 'Apparel',
            'slug' => 'apparel',
        ]);
        $collectible = MerchandiseCategory::factory()->create([
            'name' => 'Collectible',
            'slug' => 'collectible',
        ]);

        Merchandise::factory()->count(12)->sequence(
            fn ($sequence) => [
                'merchandise_category_id' => $apparel->id,
                'name' => 'Apparel Item ' . ($sequence->index + 1),
            ]
        )->create();

        Merchandise::factory()->create([
            'merchandise_category_id' => $apparel->id,
            'name' => 'Apparel Item 13',
        ]);

        Merchandise::factory()->create([
            'merchandise_category_id' => $collectible->id,
            'name' => 'Collector Poster',
        ]);

        $this->get('/merchandise?category=apparel')
            ->assertOk()
            ->assertSee('Apparel Item 1')
            ->assertDontSee('Collector Poster')
            ->assertDontSee('Apparel Item 13');

        $this->get('/merchandise?category=apparel&page=2')
            ->assertOk()
            ->assertSee('Apparel Item 13')
            ->assertDontSee('Collector Poster');

        $this->get('/merchandise?q=Poster')
            ->assertOk()
            ->assertSee('Collector Poster')
            ->assertDontSee('Apparel Item 1');
    }
}
