<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppShellTest extends TestCase
{
    public function test_welcome_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_welcome_page_uses_base_layout_with_google_fonts_and_content_slot(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
        $response->assertSee('fonts.googleapis.com', false);
        $response->assertSee('Plus+Jakarta+Sans', false);
        $response->assertSee('Newsreader', false);
        $response->assertDontSee('Archivo+Black', false);
        $response->assertDontSee('Space+Mono', false);
        $response->assertDontSee('Work+Sans', false);
        $response->assertSee('Penilaian Bahasa Cinta');
    }

    public function test_app_shell_renders_branded_top_nav_bar_and_cream_body(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('relationship project', false);
        $response->assertSee('text-terracotta', false);
        $response->assertSee('bg-cream', false);
        $response->assertSee('text-navy', false);
        $response->assertSee('<header', false);
        $response->assertSee('<svg', false);
    }
}
