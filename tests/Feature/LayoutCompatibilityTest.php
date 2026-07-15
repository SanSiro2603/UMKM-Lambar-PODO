<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class LayoutCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string> */
    private function layouts(): array
    {
        return [
            'layouts.app',
            'layouts.auth',
            'layouts.dashboard',
            'layouts.courier',
        ];
    }

    public function test_layouts_render_livewire_component_slots(): void
    {
        foreach ($this->layouts() as $layout) {
            $marker = "livewire-slot-{$layout}";
            $html = Blade::render(
                "@component('{$layout}')<div>{$marker}</div>@endcomponent"
            );

            $this->assertStringContainsString($marker, $html, "Layout [{$layout}] did not render its component slot.");
        }
    }

    public function test_layouts_keep_rendering_classic_blade_content_sections(): void
    {
        foreach ($this->layouts() as $layout) {
            $marker = "blade-section-{$layout}";
            $html = Blade::render(
                "@extends('{$layout}') @section('content')<div>{$marker}</div>@endsection"
            );

            $this->assertStringContainsString($marker, $html, "Layout [{$layout}] did not render its content section.");
        }
    }

    public function test_representative_livewire_pages_render_with_their_layouts(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke akun Anda');

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Semua Produk');

        $this->get(route('courier.tracking', ['token' => 'invalid-token']))
            ->assertOk()
            ->assertSee('Link Tidak Valid');

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total Seller');

        $seller = User::factory()->create(['role' => 'seller']);
        Store::create([
            'user_id' => $seller->id,
            'name' => 'Toko Dalam Review',
            'slug' => 'toko-dalam-review',
            'status' => 'pending',
        ]);

        $this->actingAs($seller)
            ->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('Toko Sedang Dalam Review');
    }
}
