<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_all_tables(): void
    {
        $this->seed();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseCount('news', 15);
        $this->assertDatabaseCount('testimonials', 10);
        $this->assertDatabaseCount('courses', 10);
        $this->assertDatabaseCount('calendars', 12);
        $this->assertDatabaseCount('newsletters', 5);
        $this->assertDatabaseCount('logs', 20);

        $this->assertGreaterThan(0, \DB::table('news_newsletter')->count());
        $this->assertGreaterThan(0, \DB::table('newsletter_testimonial')->count());
        $this->assertGreaterThan(0, \DB::table('course_newsletter')->count());
        $this->assertGreaterThan(0, \DB::table('calendar_newsletter')->count());
    }
}
