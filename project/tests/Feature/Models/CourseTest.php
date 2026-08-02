<?php

namespace Tests\Feature\Models;

use App\Models\Course;
use App\Models\Newsletter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_can_be_created_without_optional_fields(): void
    {
        $course = Course::create([
            'title' => 'Curso de PHP',
            'url' => 'https://example.com/curso-php',
            'imageUrl' => null,
            'location' => null,
            'schedule' => null,
            'start_date' => '2026-09-01',
            'price' => '199.90',
            'status' => 'received',
        ]);

        $this->assertDatabaseHas('courses', ['title' => 'Curso de PHP']);
        $this->assertNull($course->imageUrl);
        $this->assertNull($course->location);
        $this->assertNull($course->schedule);
    }

    public function test_course_has_no_timestamps(): void
    {
        $course = Course::factory()->create();

        $this->assertArrayNotHasKey('created_at', $course->getAttributes());
        $this->assertArrayNotHasKey('updated_at', $course->getAttributes());
    }

    public function test_course_belongs_to_many_newsletters(): void
    {
        $course = Course::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $newsletter->courses()->attach($course->id);

        $this->assertTrue($course->newsletters->contains($newsletter));
        $this->assertTrue($newsletter->courses->contains($course));
    }
}
