<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomeScreenTest extends TestCase
{
    public function test_welcome_page_returns_ok_and_renders_welcome_view(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
        $response->assertSee('Penilaian Bahasa Cinta');
        $response->assertSee('Mulai Penilaian');
        $response->assertSee('name="name"', false);
    }

    public function test_submitting_valid_name_stores_it_in_session_and_redirects_to_assessment(): void
    {
        $response = $this->post('/assessment/start', [
            'name' => 'Budi Santoso',
        ]);

        $response->assertRedirect('/assessment');
        $response->assertSessionHas('name', 'Budi Santoso');
    }

    public function test_submitting_without_name_fails_validation_and_redirects_back(): void
    {
        $response = $this->from('/')->post('/assessment/start', [
            'name' => '',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['name']);
    }

    public function test_submitting_without_name_displays_inline_validation_error_in_bahasa_indonesia(): void
    {
        $response = $this->from('/')->post('/assessment/start', [
            'name' => '',
        ]);

        $followUp = $this->get('/');
        $followUp->assertSee('Nama wajib diisi.');
    }

    public function test_submitting_name_longer_than_100_chars_fails_validation(): void
    {
        $response = $this->from('/')->post('/assessment/start', [
            'name' => str_repeat('a', 101),
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['name']);
    }

    public function test_welcome_page_uses_relationship_project_design_system_and_eyebrow(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        // Eyebrow label in terracotta, uppercase, tracked
        $response->assertSee('text-terracotta', false);
        $response->assertSee('uppercase', false);
        $response->assertSee('tracking-[0.15em]', false);
        $response->assertSee('Penilaian Relasi');

        // Display typography scale
        $response->assertSee('text-3xl', false);
        $response->assertSee('md:text-[64px]', false);
        $response->assertSee('font-medium', false);
        $response->assertSee('tracking-[-0.02em]', false);

        // Form wrapping card
        $response->assertSee('bg-white', false);
        $response->assertSee('border-line', false);
        $response->assertSee('rounded-lg', false);

        // Name input card treatment and touch target
        $response->assertSee('w-full', false);
        $response->assertSee('rounded-sm', false);
        $response->assertSee('min-h-[44px]', false);

        // Primary submit button
        $response->assertSee('bg-navy', false);
        $response->assertSee('text-cream', false);
        $response->assertSee('sm:w-auto', false);

        // No brutalist legacy tokens
        $response->assertDontSee('font-headline', false);
        $response->assertDontSee('border-black', false);
        $response->assertDontSee('border-[5px]', false);
        $response->assertDontSee('border-[3px]', false);
        $response->assertDontSee('bg-[#F0F0F0]', false);
    }

    public function test_name_input_error_state_uses_error_token_and_not_black_border(): void
    {
        $response = $this->from('/')->post('/assessment/start', [
            'name' => '',
        ]);

        $followUp = $this->get('/');
        $followUp->assertSee('border-error', false);
        $followUp->assertSee('text-error', false);
        $followUp->assertDontSee('border-black', false);
    }
}
