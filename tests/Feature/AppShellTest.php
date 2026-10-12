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
        $response->assertSee('Archivo+Black', false);
        $response->assertSee('Space+Mono', false);
        $response->assertSee('Work+Sans', false);
        $response->assertSee('RawBlock Design Shell');
    }
}
