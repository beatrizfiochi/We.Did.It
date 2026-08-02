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
            'image' => null,
            'status' => 'received',
        ]);

        $this->assertDatabaseHas('news', ['title' => 'Título da notícia']);
        $this->assertNull($news->image);
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
