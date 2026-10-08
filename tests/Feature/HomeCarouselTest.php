<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeCarouselTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_carousel_has_centered_slide_indicators_without_a_pause_button(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('.site-header { position: sticky; top: 0;', false)
            ->assertSee('data-carousel-indicator="0"', false)
            ->assertSee('data-carousel-indicator="1"', false)
            ->assertSee('data-carousel-indicator="2"', false)
            ->assertSee('.carousel-navigation { position: absolute; left: 50%', false)
            ->assertSee('width: 90%; height: clamp(288px, 49.6vw, 520px)', false)
            ->assertSee('border-radius: 18px', false)
            ->assertDontSee('data-carousel-toggle', false);
    }

    public function test_public_service_pages_have_sticky_navigation(): void
    {
        $this->get(route('credits.index'))
            ->assertOk()
            ->assertSee('class="sticky top-0 z-50 border-b', false);

        $this->get(route('savings.index'))
            ->assertOk()
            ->assertSee('class="sticky top-0 z-50 border-b', false);
    }
}
