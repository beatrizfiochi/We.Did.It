<?php

namespace Tests\Feature\Admin;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_an_image_updates_the_mirror_and_removes_the_file(): void
    {
        Storage::fake('public');

        $news = News::factory()->withImages(3)->create();
        $primeira = $news->images()->orderBy('order')->first();
        Storage::disk('public')->put($primeira->path, 'conteudo falso');

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.images.destroy', $primeira))
            ->assertSessionHasNoErrors();

        $news->refresh();

        $this->assertSame(2, $news->images()->count());
        $this->assertSame($news->image, $news->images()->orderBy('order')->first()->path);
        Storage::disk('public')->assertMissing($primeira->path);
    }

    public function test_deleting_the_last_image_leaves_the_mirror_null(): void
    {
        Storage::fake('public');

        $news = News::factory()->withImages(1)->create();
        $imagem = $news->images()->first();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.images.destroy', $imagem));

        $this->assertNull($news->fresh()->image);
    }

    public function test_guests_cannot_delete_images(): void
    {
        $news = News::factory()->withImages(1)->create();
        $imagem = $news->images()->first();

        $this->delete(route('admin.images.destroy', $imagem))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('images', 1);
    }
}
