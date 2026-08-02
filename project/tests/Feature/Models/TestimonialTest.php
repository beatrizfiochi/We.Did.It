<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Newsletter;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    public function test_testimonial_belongs_to_category(): void
    {
        $category = Category::create(['name' => 'Educação']);
        $testimonial = Testimonial::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($testimonial->category->is($category));
    }

    public function test_testimonial_can_be_attached_to_newsletter_with_order(): void
    {
        $testimonial = Testimonial::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $newsletter->testimonials()->attach($testimonial->id, ['order' => 2]);

        $this->assertSame(2, $testimonial->newsletters()->first()->pivot->order);
        $this->assertTrue($newsletter->testimonials->contains($testimonial));
    }
}
