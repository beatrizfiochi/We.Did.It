<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Course;
use App\Models\News;
use App\Models\Newsletter;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class NewsletterSeeder extends Seeder
{
    public function run(): void
    {
        Newsletter::factory(5)->create()->each(function (Newsletter $newsletter) {
            News::where('status', 'accepted')->inRandomOrder()->take(random_int(2, 4))->get()->each(
                fn (News $news, int $index) => $newsletter->news()->attach($news->id, ['order' => $index + 1])
            );

            Testimonial::where('status', 'accepted')->inRandomOrder()->take(random_int(1, 3))->get()->each(
                fn (Testimonial $testimonial, int $index) => $newsletter->testimonials()->attach($testimonial->id, ['order' => $index + 1])
            );

            $newsletter->courses()->attach(
                Course::inRandomOrder()->take(random_int(1, 3))->pluck('id')
            );

            $newsletter->calendars()->attach(
                Calendar::inRandomOrder()->take(random_int(1, 3))->pluck('id')
            );
        });
    }
}
