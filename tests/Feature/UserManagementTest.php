<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_peserta_and_return_to_author_index()
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Peserta Baru',
                'email' => 'peserta@example.com',
                'no_hp' => '081234567890',
                'password' => 'secret123',
                'role' => 'peserta',
            ])
            ->assertRedirect(route('users.index.author'));

        $user = User::where('email', 'peserta@example.com')->firstOrFail();

        $this->assertSame('peserta', $user->role);
        $this->assertSame('081234567890', $user->no_hp);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_admin_must_choose_category_when_creating_jury()
    {
        $admin = User::factory()->role('admin')->create();

        $this->actingAs($admin)
            ->from(route('users.create'))
            ->post(route('users.store'), [
                'name' => 'Juri Baru',
                'email' => 'juri@example.com',
                'password' => 'secret123',
                'role' => 'juri',
            ])
            ->assertRedirect(route('users.create'))
            ->assertSessionHasErrors('category_id');
    }

    public function test_admin_can_create_jury_with_category_assignment()
    {
        $admin = User::factory()->role('admin')->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Juri Kategori',
                'email' => 'juri-kategori@example.com',
                'password' => 'secret123',
                'role' => 'juri',
                'category_id' => $category->id,
            ])
            ->assertRedirect(route('users.index.kurator'));

        $jury = User::where('email', 'juri-kategori@example.com')->firstOrFail();

        $this->assertSame('juri', $jury->role);
        $this->assertSame($category->id, $jury->category_id);
    }
}
