<?php

namespace Tests\Feature\Models;

use App\Models\Calendar;
use App\Models\Course;
use App\Models\News;
use App\Models\Newsletter;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_newsletter_aggregates_all_related_content(): void
    {
        $newsletter = Newsletter::factory()->create();

        $news = News::factory()->create();
        $testimonial = Testimonial::factory()->create();
        $course = Course::factory()->create();
        $calendar = Calendar::factory()->create();

        $newsletter->news()->attach($news->id, ['order' => 1]);
        $newsletter->testimonials()->attach($testimonial->id, ['order' => 1]);
        $newsletter->courses()->attach($course->id);
        $newsletter->calendars()->attach($calendar->id);

        $this->assertCount(1, $newsletter->news);
        $this->assertCount(1, $newsletter->testimonials);
        $this->assertCount(1, $newsletter->courses);
        $this->assertCount(1, $newsletter->calendars);
    }
}
