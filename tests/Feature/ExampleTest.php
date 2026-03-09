<?php

namespace Tests\Feature;

use App\Models\SitePage;
use Database\Seeders\SitePageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SitePageSeeder::class);
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee('A platform core for modular multiplayer games.')
            ->assertSee('Admin Sign In')
            ->assertSee('Home');
    }

    public function test_public_page_body_renders_markdown_safely(): void
    {
        $page = SitePage::where('slug', SitePage::SLUG_HOME)->firstOrFail();

        $page->update([
            'body' => "# Intro\n\n**Bold text** and [Safe Link](https://example.com)\n\n<script>alert('x')</script>",
        ]);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('<h1>Intro</h1>', false)
            ->assertSee('<strong>Bold text</strong>', false)
            ->assertSee('https://example.com', false)
            ->assertDontSee('<script>', false);
    }
}
