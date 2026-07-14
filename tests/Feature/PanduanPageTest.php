<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanduanPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_panduan_page_is_public_and_defaults_to_seller_tab(): void
    {
        $this->get(route('panduan'))
            ->assertOk()
            ->assertSee('data-initial-tab="seller"', false)
            ->assertSee('Tutorial Seller')
            ->assertSee('Tutorial Customer')
            ->assertSee('role="tablist"', false)
            ->assertSee('aria-controls="panduan-panel-seller"', false);
    }

    public function test_customer_query_selects_customer_tab_and_invalid_value_falls_back_to_seller(): void
    {
        $this->get(route('panduan', ['tab' => 'customer']))
            ->assertOk()
            ->assertSee('data-initial-tab="customer"', false);

        $this->get(route('panduan', ['tab' => 'invalid']))
            ->assertOk()
            ->assertSee('data-initial-tab="seller"', false);
    }

    public function test_panduan_navigation_and_footer_use_the_public_route(): void
    {
        $response = $this->get(route('panduan'));

        $response
            ->assertSee('href="' . route('panduan') . '"', false)
            ->assertSee('href="' . route('panduan', ['tab' => 'customer']) . '"', false)
            ->assertSee('href="' . route('panduan', ['tab' => 'seller']) . '"', false);
    }

    public function test_home_no_longer_contains_the_tutorial_section(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="tutorial"', false)
            ->assertSee('Jelajahi Kategori');
    }
}
