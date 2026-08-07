<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\News;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created(): void
    {
        $category = Category::create(['name' => 'Tecnologia']);

        $this->assertDatabaseHas('categories', ['name' => 'Tecnologia']);
        $this->assertSame('Tecnologia', $category->name);
    }

    public function test_category_has_many_news(): void
    {
        $category = Category::create(['name' => 'Educação']);
        News::factory(2)->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->news);
    }

    public function test_category_has_many_testimonials(): void
    {
        $category = Category::create(['name' => 'Educação']);
        Testimonial::factory(3)->create(['category_id' => $category->id]);

        $this->assertCount(3, $category->testimonials);
    }
}
