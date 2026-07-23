<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_actionable_empty_states(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kategori sedang disiapkan')
            ->assertSee('Belum ada produk tersedia')
            ->assertSee('Belum ada toko aktif')
            ->assertSee('Lihat Semua Produk')
            ->assertSee('Pelajari Cara Belanja')
            ->assertSee('Daftar Sebagai Penjual');
    }

    public function test_home_handles_long_unicode_content_large_numbers_and_escapes_html(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $storeName = 'Toko 測試 ☕ '.str_repeat('Lampung ', 24);
        $productName = 'Produk 🌾 '.str_repeat('SangatPanjang ', 17);
        $categoryName = '農業 Kategori '.str_repeat('Panjang ', 25);
        $unsafeDescription = '<script>alert("x")</script> '.str_repeat('Deskripsi panjang ', 70);

        $store = Store::create([
            'user_id' => $seller->id,
            'name' => $storeName,
            'slug' => 'toko-unicode-panjang',
            'description' => $unsafeDescription,
            'status' => 'approved',
        ]);
        $category = Category::create([
            'name' => $categoryName,
            'slug' => 'kategori-unicode-panjang',
            'icon' => 'box',
        ]);
        Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => $productName,
            'slug' => 'produk-unicode-panjang',
            'price' => 4294967295,
            'stock' => 1,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($storeName)
            ->assertSee($productName)
            ->assertSee($categoryName)
            ->assertSee('Rp 4.294.967.295')
            ->assertSee('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("x")</script>', false);
    }

    public function test_public_layout_exposes_accessible_navigation_search_dialog_and_cart_contracts(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('href="#main-content"', false)
            ->assertSee('id="main-content"', false)
            ->assertSee('aria-label="Navigasi utama"', false)
            ->assertSee('action="'.route('products.index').'" method="GET"', false)
            ->assertSee('name="q"', false)
            ->assertSee('name="cat"', false)
            ->assertSee('aria-controls="mobile-navigation"', false)
            ->assertSee('role="dialog" aria-modal="true"', false)
            ->assertSee('x-trap.noscroll="show"', false)
            ->assertSee('aria-label="Keranjang, 0 item"', false);
    }

    public function test_footer_only_renders_valid_destinations(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('FAQ & Panduan', false)
            ->assertSee('href="'.route('panduan').'"', false)
            ->assertDontSee('href="#"', false)
            ->assertDontSee('Hubungi Kami')
            ->assertDontSee('Syarat & Ketentuan')
            ->assertDontSee('Kebijakan Privasi')
            ->assertDontSee('+62 812-xxxx-xxxx');
    }
}
