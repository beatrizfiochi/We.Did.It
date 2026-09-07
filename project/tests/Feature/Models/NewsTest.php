<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\News;
use App\Models\Newsletter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_can_be_created_with_fillable_fields(): void
    {
        $category = Category::create(['name' => 'Tecnologia']);

        $news = News::create([
            'category_id' => $category->id,
            'title' => 'Título da notícia',
            'description' => 'Descrição da notícia',
            'event_start_date' => '2026-05-12',
            'image' => null,
            'status' => 'received',
        ]);

        $this->assertDatabaseHas('news', ['title' => 'Título da notícia']);
        $this->assertNull($news->image);
    }

    public function test_news_accepts_all_status_enum_values(): void
    {
        $accepted = News::factory()->create(['status' => 'accepted']);
        $refused = News::factory()->create(['status' => 'refused']);

        $this->assertSame('accepted', $accepted->fresh()->status);
        $this->assertSame('refused', $refused->fresh()->status);
    }

    public function test_news_belongs_to_category(): void
    {
        $category = Category::create(['name' => 'Tecnologia']);
        $news = News::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($news->category->is($category));
    }

    public function test_news_can_be_attached_to_newsletter_with_order(): void
    {
        $news = News::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $newsletter->news()->attach($news->id, ['order' => 1]);

        $this->assertSame(1, (int) $news->newsletters()->first()->pivot->order);
        $this->assertTrue($newsletter->news->contains($news));
    }
}
