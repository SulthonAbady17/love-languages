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
}
