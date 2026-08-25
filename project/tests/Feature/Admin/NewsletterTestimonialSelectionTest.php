<?php

namespace Tests\Feature\Admin;

use App\Models\Newsletter;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Seleção dos testemunhos para a newsletter (SCRUM-103).
 *
 * Não há teste de 200 ao ecrã: o editTestimonials renderiza
 * Admin/Newsletters/Testimonials, que é a SCRUM-109 da Leida e ainda não existe.
 * Testá-lo agora daria 500. Fica do lado de quem faz o ecrã.
 */
class NewsletterTestimonialSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_the_testimonial_selection(): void
    {
        $newsletter = Newsletter::factory()->create();

        $this->get(route('admin.newsletters.testimonials.edit', $newsletter))->assertRedirect(route('login'));
        $this->put(route('admin.newsletters.testimonials.update', $newsletter), ['testimonial_ids' => []])
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_select_testimonials_for_a_newsletter(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = Testimonial::factory()->count(3)->create(['status' => 'accepted']);

        $response = $this->actingAs($admin)
            ->from(route('admin.newsletters.testimonials.edit', $newsletter))
            ->put(route('admin.newsletters.testimonials.update', $newsletter), [
                'testimonial_ids' => $news->pluck('id')->all(),
            ]);

        $this->assertEqualsCanonicalizing(
            $news->pluck('id')->all(),
            $newsletter->fresh()->testimonials->pluck('id')->all()
        );
        $response->assertRedirect(route('admin.newsletters.testimonials.edit', $newsletter));
        $response->assertSessionHas('success');
    }

    public function test_the_chosen_order_is_kept(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $primeira = Testimonial::factory()->create(['status' => 'accepted']);
        $segunda = Testimonial::factory()->create(['status' => 'accepted']);

        // enviadas ao contrário da ordem dos ids, para o teste não passar por acaso
        $this->actingAs($admin)->put(route('admin.newsletters.testimonials.update', $newsletter), [
            'testimonial_ids' => [$segunda->id, $primeira->id],
        ]);

        $this->assertSame(
            [$segunda->id, $primeira->id],
            $newsletter->fresh()->testimonials()->orderByPivot('order')->pluck('testimonials.id')->all()
        );
    }

    public function test_a_new_selection_replaces_the_previous_one(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $antiga = Testimonial::factory()->create(['status' => 'accepted']);
        $nova = Testimonial::factory()->create(['status' => 'accepted']);
        $newsletter->testimonials()->attach($antiga->id, ['order' => 1]);

        $this->actingAs($admin)->put(route('admin.newsletters.testimonials.update', $newsletter), [
            'testimonial_ids' => [$nova->id],
        ]);

        $this->assertSame([$nova->id], $newsletter->fresh()->testimonials->pluck('id')->all());
    }

    public function test_submitting_without_testimonial_ids_clears_the_selection(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $news = Testimonial::factory()->create(['status' => 'accepted']);
        $newsletter->testimonials()->attach($news->id, ['order' => 1]);

        $this->actingAs($admin)->put(route('admin.newsletters.testimonials.update', $newsletter), []);

        $this->assertCount(0, $newsletter->testimonials()->get());
    }

    public function test_testimonials_that_are_not_approved_cannot_be_selected(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();
        $porModerar = Testimonial::factory()->create(['status' => 'received']);
        $recusada = Testimonial::factory()->create(['status' => 'refused']);

        // a regra vive no servidor e não só no ecrã: um pedido feito à mão ao
        // endpoint não pode meter na newsletter um testemunho que ninguém aprovou
        $response = $this->actingAs($admin)->put(route('admin.newsletters.testimonials.update', $newsletter), [
            'testimonial_ids' => [$porModerar->id, $recusada->id],
        ]);

        $response->assertSessionHasErrors(['testimonial_ids.0', 'testimonial_ids.1']);
        $this->assertCount(0, $newsletter->testimonials()->get());
    }

    public function test_selecting_a_testimonial_that_does_not_exist_fails_validation(): void
    {
        $admin = User::factory()->create();
        $newsletter = Newsletter::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.newsletters.testimonials.update', $newsletter), [
            'testimonial_ids' => [99999],
        ]);

        $response->assertSessionHasErrors('testimonial_ids.0');
    }
}
