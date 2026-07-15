<?php

namespace Tests\Feature;

use App\Models\Film;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageFallbackTest extends TestCase
{
    public function test_storage_route_serves_files_from_the_public_disk()
    {
        Storage::fake('public');
        Storage::disk('public')->put('posters/uploaded-poster.txt', 'poster-ok');

        $response = $this->get('/storage/posters/uploaded-poster.txt');

        $response->assertOk();
        $this->assertSame('poster-ok', $response->streamedContent());
    }

    public function test_film_poster_url_normalizes_storage_prefixed_paths()
    {
        $film = new Film();
        $film->poster = 'storage/posters/uploaded-poster.jpg';

        $this->assertSame(url('storage/posters/uploaded-poster.jpg'), $film->poster_url);
    }

    public function test_storage_route_rejects_directory_traversal_attempts()
    {
        $this->get('/storage/../.env')->assertNotFound();
    }
}
