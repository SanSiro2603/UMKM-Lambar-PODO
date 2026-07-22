<?php

namespace Tests\Feature;

use App\Livewire\Admin\Sellers;
use App\Livewire\Auth\Login;
use App\Livewire\StoreDetail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StorePaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SellerModerationWhatsappTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_suspend_and_reactivate_seller(): void
    {
        $admin = $this->user('admin');
        $store = $this->store();

        Livewire::actingAs($admin)->test(Sellers::class)
            ->call('showStore', $store->id)
            ->call('openSuspendModal')
            ->set('suspensionReason', 'Produk tidak sesuai kategori pertanian.')
            ->call('suspendStore')
            ->assertHasNoErrors()
            ->assertSee('Seller Dinonaktifkan');

        $store->refresh();
        $this->assertSame('suspended', $store->status);
        $this->assertSame('Produk tidak sesuai kategori pertanian.', $store->suspension_reason);
        $this->assertSame($admin->id, $store->suspended_by);
        $this->get(route('stores.show', $store->slug))->assertNotFound();

        $reactivation = Livewire::actingAs($admin)->test(Sellers::class)
            ->call('showStore', $store->id)
            ->assertDontSeeHtml('wire:confirm=')
            ->call('openReactivateModal')
            ->assertSet('showReactivateModal', true)
            ->assertSee('Aktifkan kembali '.$store->name.'?');

        $this->assertSame('suspended', $store->fresh()->status);

        $reactivation->call('closeReactivateModal')
            ->assertSet('showReactivateModal', false)
            ->call('openReactivateModal')
            ->call('reactivateStore')
            ->assertHasNoErrors()
            ->assertSet('showReactivateModal', false);

        $this->assertSame('approved', $store->fresh()->status);
        $this->get(route('stores.show', $store->slug))->assertOk();
    }

    public function test_suspended_seller_cannot_login_or_keep_an_existing_session(): void
    {
        $store = $this->store();
        $seller = $store->user;
        $store->update([
            'status' => 'suspended',
            'suspension_reason' => 'Pelanggaran kebijakan marketplace.',
            'suspended_at' => now(),
        ]);

        $this->attemptLogin($seller);

        $this->assertSame([
            'title' => 'Akun Seller Dinonaktifkan',
            'reason' => 'Pelanggaran kebijakan marketplace.',
        ], session('seller_suspended'));

        $this->assertGuest();

        $this->actingAs($seller)
            ->get(route('home'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('seller_suspended.reason', 'Pelanggaran kebijakan marketplace.');

        $this->assertGuest();
    }

    public function test_suspended_login_uses_fallback_when_reason_is_empty(): void
    {
        $store = $this->store();
        $store->update([
            'status' => 'suspended',
            'suspension_reason' => null,
            'suspended_at' => now(),
        ]);

        $this->attemptLogin($store->user);

        $this->assertSame('Tidak ada alasan tambahan dari admin.', session('seller_suspended.reason'));
        $this->assertGuest();
    }

    public function test_wrong_credentials_do_not_reveal_suspension_reason(): void
    {
        $store = $this->store();
        $store->update([
            'status' => 'suspended',
            'suspension_reason' => 'Alasan ini bersifat privat.',
        ]);

        Livewire::test(Login::class)
            ->set('email', $store->user->email)
            ->set('password', 'password-salah')
            ->call('login')
            ->assertSee('Email atau kata sandi salah.')
            ->assertDontSee('Alasan ini bersifat privat.');

        $this->assertNull(session('seller_suspended'));
        $this->assertGuest();
    }

    public function test_login_page_renders_admin_suspension_reason(): void
    {
        $this->withSession([
            'seller_suspended' => [
                'title' => 'Akun Seller Dinonaktifkan',
                'reason' => 'Produk yang dijual tidak sesuai ketentuan pertanian.',
            ],
        ])->get(route('login'))
            ->assertOk()
            ->assertSee('Akun Seller Dinonaktifkan')
            ->assertSee('Alasan admin')
            ->assertSee('Produk yang dijual tidak sesuai ketentuan pertanian.');
    }

    public function test_non_admin_cannot_open_seller_administration(): void
    {
        $seller = $this->store()->user;

        $this->actingAs($seller)->get(route('admin.sellers'))->assertForbidden();
    }

    public function test_permanent_delete_requires_reason_and_exact_store_name(): void
    {
        $admin = $this->user('admin');
        $store = $this->store();

        Livewire::actingAs($admin)->test(Sellers::class)
            ->call('showStore', $store->id)
            ->call('openDeleteModal')
            ->set('deleteReason', 'abc')
            ->set('deleteConfirmation', 'Nama yang salah')
            ->call('deleteStorePermanently')
            ->assertHasErrors(['deleteReason']);

        Livewire::actingAs($admin)->test(Sellers::class)
            ->call('showStore', $store->id)
            ->call('openDeleteModal')
            ->set('deleteReason', 'Pelanggaran kebijakan marketplace.')
            ->set('deleteConfirmation', 'Nama yang salah')
            ->call('deleteStorePermanently')
            ->assertHasErrors(['deleteConfirmation']);

        $this->assertDatabaseHas('stores', ['id' => $store->id]);
    }

    public function test_permanent_delete_removes_relations_and_public_files_even_with_transactions(): void
    {
        Storage::fake('public');

        $admin = $this->user('admin');
        $store = $this->store();
        $sellerId = $store->user_id;
        $product = $this->product($store);
        $customer = $this->user('customer');
        $order = $this->order($customer, $product);

        $transaction = Transaction::create([
            'order_id' => $order->id,
            'seller_id' => $sellerId,
            'total_amount' => $order->total_price,
            'platform_fee' => 2500,
            'seller_amount' => $order->total_price - 2500,
            'status' => 'paid',
        ]);

        $paymentMethod = StorePaymentMethod::create([
            'store_id' => $store->id,
            'type' => 'bank',
            'name' => 'BRI',
            'account_name' => 'Seller Uji',
            'account_number' => '1234567890',
            'qr_code' => 'payments/qr.png',
        ]);

        $store->update([
            'logo' => 'stores/logo.png',
            'banner' => 'stores/banner.png',
            'ktp_photo' => 'stores/ktp.png',
        ]);
        $product->update(['image' => 'products/item.png']);

        foreach (['stores/logo.png', 'stores/banner.png', 'stores/ktp.png', 'products/item.png', 'payments/qr.png'] as $path) {
            Storage::disk('public')->put($path, 'test');
        }

        Livewire::actingAs($admin)->test(Sellers::class)
            ->call('showStore', $store->id)
            ->call('openDeleteModal')
            ->set('deleteReason', 'Seller terbukti melanggar ketentuan platform.')
            ->set('deleteConfirmation', $store->name)
            ->call('deleteStorePermanently')
            ->assertHasNoErrors()
            ->assertSet('view', 'list');

        $this->assertDatabaseMissing('users', ['id' => $sellerId]);
        $this->assertDatabaseMissing('stores', ['id' => $store->id]);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
        $this->assertDatabaseMissing('store_payment_methods', ['id' => $paymentMethod->id]);

        foreach (['stores/logo.png', 'stores/banner.png', 'stores/ktp.png', 'products/item.png', 'payments/qr.png'] as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_store_contact_builds_whatsapp_url_from_selected_topic_and_note(): void
    {
        $store = $this->store(phone: '081234567890');

        Livewire::test(StoreDetail::class, ['slug' => $store->slug])
            ->assertSee('Hubungi Seller')
            ->call('openContactModal')
            ->assertSet('showContactModal', true)
            ->set('contactTopic', 'complaint')
            ->set('contactNote', 'Pesanan ORD-123 belum sampai.')
            ->call('contactSeller')
            ->assertHasNoErrors()
            ->assertDispatched('open-whatsapp', function (string $name, array $params) use ($store): bool {
                $decodedUrl = rawurldecode($params['url']);

                return str_starts_with($params['url'], 'https://wa.me/6281234567890?text=')
                    && str_contains($decodedUrl, 'Keluhan/komplain')
                    && str_contains($decodedUrl, 'Pesanan ORD-123 belum sampai.')
                    && str_contains($decodedUrl, route('stores.show', $store->slug));
            });
    }

    public function test_store_contact_validates_topic_note_and_missing_number(): void
    {
        $store = $this->store(phone: 'nomor-tidak-valid');

        Livewire::test(StoreDetail::class, ['slug' => $store->slug])
            ->assertSee('WhatsApp Tidak Tersedia')
            ->call('openContactModal')
            ->assertHasErrors(['whatsapp']);

        $validStore = $this->store(phone: '6281234567890');
        Livewire::test(StoreDetail::class, ['slug' => $validStore->slug])
            ->call('openContactModal')
            ->set('contactNote', str_repeat('a', 501))
            ->call('contactSeller')
            ->assertHasErrors(['contactTopic', 'contactNote']);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create(['phone' => '081234567890']);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function attemptLogin(User $seller): void
    {
        $component = Livewire::test(Login::class)->instance();
        $component->email = $seller->email;
        $component->password = 'password';

        $session = app('session')->driver();
        $session->start();
        $request = Request::create(route('login'), 'POST');
        $request->setLaravelSession($session);
        app()->instance('request', $request);

        $component->login();
    }

    private function store(string $status = 'approved', string $phone = '081234567890'): Store
    {
        $seller = $this->user('seller');
        $seller->update(['phone' => $phone]);
        $suffix = Str::lower(Str::random(8));

        return Store::create([
            'user_id' => $seller->id,
            'name' => 'Toko Moderasi '.$suffix,
            'slug' => 'toko-moderasi-'.$suffix,
            'description' => 'Toko produk pertanian lokal.',
            'address' => 'Kabupaten Lampung Barat',
            'status' => $status,
        ]);
    }

    private function product(Store $store): Product
    {
        $suffix = Str::lower(Str::random(8));
        $category = Category::create([
            'name' => 'Pertanian '.$suffix,
            'slug' => 'pertanian-'.$suffix,
            'icon' => 'store',
        ]);

        return Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Kopi '.$suffix,
            'slug' => 'kopi-'.$suffix,
            'description' => 'Produk kopi lokal.',
            'price' => 50000,
            'stock' => 10,
        ]);
    }

    private function order(User $customer, Product $product): Order
    {
        $order = Order::create([
            'order_code' => 'ORD-'.Str::upper(Str::random(10)),
            'customer_id' => $customer->id,
            'store_id' => $product->store_id,
            'total_price' => 55000,
            'shipping_cost' => 5000,
            'shipping_address' => 'Alamat pelanggan',
            'shipping_phone' => '081234567890',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'status' => 'delivered',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => $product->price,
        ]);

        return $order;
    }
}
