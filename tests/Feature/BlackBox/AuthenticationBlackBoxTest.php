<?php

namespace Tests\Feature\BlackBox;

use App\Livewire\Auth\Login;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class AuthenticationBlackBoxTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Password-Uji-123!';
    private const RATE_KEY = 'login:127.0.0.1';

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear(self::RATE_KEY);
    }

    #[TestDox('AUT-001 Halaman login dapat dibuka pengunjung')]
    public function test_aut_001_login_page_is_public(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Selamat Datang Kembali!')
            ->assertSee('Ingat saya');
    }

    #[TestDox('AUT-002 Email dan password kosong ditolak')]
    public function test_aut_002_empty_credentials_are_rejected(): void
    {
        Livewire::test(Login::class)
            ->call('login')
            ->assertHasErrors(['email' => 'required', 'password' => 'required']);

        $this->assertGuest();
    }

    #[TestDox('AUT-003 Format email tidak valid ditolak')]
    public function test_aut_003_invalid_email_format_is_rejected(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'bukan-email')
            ->set('password', self::PASSWORD)
            ->call('login')
            ->assertHasErrors(['email' => 'email']);

        $this->assertGuest();
    }

    #[TestDox('AUT-004 Pesan validasi login tidak menampilkan key internal')]
    public function test_aut_004_validation_message_is_user_friendly(): void
    {
        Livewire::test(Login::class)
            ->call('login')
            ->assertDontSee('validation.required');
    }

    #[TestDox('AUT-005 Kredensial salah ditolak dengan pemberitahuan')]
    public function test_aut_005_wrong_credentials_are_rejected(): void
    {
        $user = $this->createUser('customer');

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'Password-Salah')
            ->call('login')
            ->assertSee('Email atau kata sandi salah.');

        $this->assertGuest();
    }

    #[TestDox('AUT-006 Pelanggan valid diarahkan ke beranda')]
    public function test_aut_006_customer_login_redirects_home(): void
    {
        $customer = $this->createUser('customer');

        $response = $this->performSuccessfulLogin($customer);

        $this->assertSame(route('home'), $response->getTargetUrl());
        $this->assertAuthenticatedAs($customer);
    }

    #[TestDox('AUT-007 Admin valid diarahkan ke dashboard admin')]
    public function test_aut_007_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->performSuccessfulLogin($admin);

        $this->assertSame(route('admin.dashboard'), $response->getTargetUrl());
        $this->assertAuthenticatedAs($admin);
    }

    #[TestDox('AUT-008 Seller approved diarahkan ke dashboard seller')]
    public function test_aut_008_approved_seller_login_redirects_to_seller_dashboard(): void
    {
        $seller = $this->createSeller('approved');

        $response = $this->performSuccessfulLogin($seller);

        $this->assertSame(route('seller.dashboard'), $response->getTargetUrl());
        $this->assertAuthenticatedAs($seller);
    }

    #[TestDox('AUT-009 Seller pending dapat login dan melihat status review')]
    public function test_aut_009_pending_seller_sees_review_dashboard(): void
    {
        $seller = $this->createSeller('pending');

        $response = $this->performSuccessfulLogin($seller);
        $this->assertSame(route('seller.dashboard'), $response->getTargetUrl());

        $this->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('Toko Sedang Dalam Review');
    }

    #[TestDox('AUT-010 Seller rejected dikeluarkan dan diberi pemberitahuan')]
    public function test_aut_010_rejected_seller_is_logged_out_with_notice(): void
    {
        $seller = $this->createSeller('rejected');

        $response = $this->performSuccessfulLogin($seller);
        $this->assertSame(route('seller.dashboard'), $response->getTargetUrl());

        $this->get(route('seller.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', 'Akun toko Anda telah ditolak oleh admin. Silakan hubungi admin untuk informasi lebih lanjut.');
        $this->assertGuest();
    }

    #[TestDox('AUT-011 Remember me membuat token pengingat')]
    public function test_aut_011_remember_me_creates_remember_token(): void
    {
        $customer = $this->createUser('customer', ['remember_token' => null]);

        $this->performSuccessfulLogin($customer, true);

        $this->assertNotNull($customer->fresh()->remember_token);
    }

    #[TestDox('AUT-012 Login tanpa remember me tidak membuat token pengingat')]
    public function test_aut_012_login_without_remember_does_not_create_token(): void
    {
        $customer = $this->createUser('customer', ['remember_token' => null]);

        $this->performSuccessfulLogin($customer, false);

        $this->assertNull($customer->fresh()->remember_token);
    }

    #[TestDox('AUT-013 Percobaan login keenam dalam satu menit dibatasi')]
    public function test_aut_013_sixth_failed_login_attempt_is_rate_limited(): void
    {
        $user = $this->createUser('customer');

        foreach (range(1, 5) as $attempt) {
            Livewire::test(Login::class)
                ->set('email', $user->email)
                ->set('password', 'salah-'.$attempt)
                ->call('login');
        }

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'masih-salah')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertSame(5, RateLimiter::attempts(self::RATE_KEY));
        $this->assertGuest();
    }

    #[TestDox('AUT-014 Login berhasil menghapus hitungan percobaan gagal')]
    public function test_aut_014_successful_login_clears_rate_limit(): void
    {
        $user = $this->createUser('customer');
        RateLimiter::hit(self::RATE_KEY, 60);
        RateLimiter::hit(self::RATE_KEY, 60);

        $this->performSuccessfulLogin($user);

        $this->assertSame(0, RateLimiter::attempts(self::RATE_KEY));
    }

    #[TestDox('AUT-015 Login menghormati halaman tujuan sebelum autentikasi')]
    public function test_aut_015_login_honors_intended_destination(): void
    {
        $customer = $this->createUser('customer');
        $this->get(route('customer.orders'))->assertRedirect(route('login'));

        $response = $this->performSuccessfulLogin($customer);
        $this->assertSame(route('customer.orders'), $response->getTargetUrl());
    }

    #[TestDox('AUT-016 Logout mengakhiri autentikasi dan kembali ke beranda')]
    public function test_aut_016_logout_ends_session(): void
    {
        $customer = $this->createUser('customer');

        $this->actingAs($customer)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    #[TestDox('AUT-017 Pengguna terautentikasi tidak dapat membuka halaman login')]
    public function test_aut_017_authenticated_user_cannot_open_login(): void
    {
        $customer = $this->createUser('customer');

        $this->actingAs($customer)
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }

    #[TestDox('AUT-018 Pengguna terautentikasi tidak dapat membuka formulir registrasi')]
    public function test_aut_018_authenticated_user_cannot_open_registration_pages(): void
    {
        $customer = $this->createUser('customer');

        $this->actingAs($customer)->get(route('register'))->assertRedirect(route('home'));
        $this->actingAs($customer)->get(route('register.seller'))->assertRedirect(route('home'));
    }

    #[TestDox('AUT-019 Pelanggan ditolak dari halaman seller dan admin')]
    public function test_aut_019_customer_cannot_access_seller_or_admin_pages(): void
    {
        $customer = $this->createUser('customer');

        $this->actingAs($customer)->get(route('seller.dashboard'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($customer)->get(route('customer.dashboard'))->assertOk();
    }

    #[TestDox('AUT-020 Seller ditolak dari halaman pelanggan dan admin')]
    public function test_aut_020_seller_cannot_access_customer_or_admin_pages(): void
    {
        $seller = $this->createSeller('approved');

        $this->actingAs($seller)->get(route('customer.dashboard'))->assertForbidden();
        $this->actingAs($seller)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($seller)->get(route('seller.dashboard'))->assertOk();
    }

    #[TestDox('AUT-021 Admin ditolak dari halaman pelanggan dan seller')]
    public function test_aut_021_admin_cannot_access_customer_or_seller_pages(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)->get(route('customer.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('seller.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    #[TestDox('AUT-022 Seller pending dibatasi dari fitur khusus seller approved')]
    public function test_aut_022_pending_seller_is_blocked_from_approved_features(): void
    {
        $seller = $this->createSeller('pending');

        foreach (['seller.products', 'seller.orders', 'seller.reports', 'seller.profile', 'seller.bank.index'] as $routeName) {
            $this->actingAs($seller)->get(route($routeName))->assertRedirect(route('seller.dashboard'));
        }
    }

    #[TestDox('AUT-023 Seller approved dapat membuka fitur seller terproteksi')]
    public function test_aut_023_approved_seller_can_access_protected_features(): void
    {
        $seller = $this->createSeller('approved');

        foreach (['seller.products', 'seller.orders', 'seller.reports', 'seller.profile', 'seller.bank.index'] as $routeName) {
            $this->actingAs($seller)->get(route($routeName))->assertOk();
        }
    }

    private function createUser(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'password' => self::PASSWORD,
            'address' => 'Kabupaten Lampung Barat',
            'phone' => '081234567890',
        ], $attributes));
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    private function createSeller(string $status): User
    {
        $seller = $this->createUser('seller');

        Store::create([
            'user_id' => $seller->id,
            'name' => 'Toko '.$status,
            'slug' => 'toko-'.$status.'-'.str()->lower(str()->random(5)),
            'description' => 'Toko untuk pengujian autentikasi',
            'address' => 'Kabupaten Lampung Barat',
            'status' => $status,
        ]);

        return $seller;
    }

    private function performSuccessfulLogin(User $user, bool $remember = false): mixed
    {
        $component = Livewire::test(Login::class)->instance();
        $component->email = $user->email;
        $component->password = self::PASSWORD;
        $component->remember = $remember;

        $session = app('session')->driver();
        $session->start();

        $request = Request::create(route('login'), 'POST');
        $request->setLaravelSession($session);
        app()->instance('request', $request);

        return $component->login();
    }
}
