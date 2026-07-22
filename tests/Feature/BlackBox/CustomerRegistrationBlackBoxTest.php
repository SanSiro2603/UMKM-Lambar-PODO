<?php

namespace Tests\Feature\BlackBox;

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class CustomerRegistrationBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createBlackBoxRegions();
    }

    #[TestDox('PLG-REG-001 Form registrasi pelanggan dapat dibuka')]
    public function test_plg_reg_001_registration_page_is_public(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Buat Akun Baru');
    }

    #[TestDox('PLG-REG-002 Seluruh field wajib divalidasi')]
    public function test_plg_reg_002_required_fields_are_validated(): void
    {
        Livewire::test(Register::class)
            ->call('register')
            ->assertHasErrors([
                'name' => 'required', 'email' => 'required', 'phone' => 'required',
                'password' => 'required', 'terms' => 'accepted', 'districtCode' => 'required',
                'villageCode' => 'required', 'detailAddress' => 'required',
            ]);
    }

    #[TestDox('PLG-REG-003 Format email tidak valid ditolak')]
    public function test_plg_reg_003_invalid_email_is_rejected(): void
    {
        $this->validComponent()->set('email', 'email-salah')->call('register')->assertHasErrors(['email' => 'email']);
    }

    #[TestDox('PLG-REG-004 Email yang sudah digunakan ditolak')]
    public function test_plg_reg_004_duplicate_email_is_rejected(): void
    {
        $existing = User::factory()->create();
        $this->validComponent()->set('email', $existing->email)->call('register')->assertHasErrors(['email' => 'unique']);
    }

    #[TestDox('PLG-REG-005 Nomor telepon lebih dari 15 karakter ditolak')]
    public function test_plg_reg_005_phone_over_maximum_is_rejected(): void
    {
        $this->validComponent()->set('phone', '0812345678901234')->call('register')->assertHasErrors(['phone' => 'max']);
    }

    #[TestDox('PLG-REG-006 Nomor telepon berisi huruf ditolak')]
    public function test_plg_reg_006_non_numeric_phone_is_rejected(): void
    {
        $this->validComponent()->set('phone', 'telepon-salah')->call('register')->assertHasErrors(['phone']);
    }

    #[TestDox('PLG-REG-007 Password kurang dari 8 karakter ditolak')]
    public function test_plg_reg_007_short_password_is_rejected(): void
    {
        $this->validComponent()
            ->set('password', 'Pendek1')
            ->set('password_confirmation', 'Pendek1')
            ->call('register')
            ->assertHasErrors(['password' => 'min']);
    }

    #[TestDox('PLG-REG-008 Konfirmasi password harus sama')]
    public function test_plg_reg_008_password_confirmation_must_match(): void
    {
        $this->validComponent()->set('password_confirmation', 'Berbeda123')->call('register')->assertHasErrors(['password' => 'confirmed']);
    }

    #[TestDox('PLG-REG-009 Persetujuan syarat wajib dicentang')]
    public function test_plg_reg_009_terms_must_be_accepted(): void
    {
        $this->validComponent()->set('terms', false)->call('register')->assertHasErrors(['terms' => 'accepted']);
    }

    #[TestDox('PLG-REG-010 Kode wilayah di luar Lampung Barat ditolak')]
    public function test_plg_reg_010_outside_region_codes_are_rejected(): void
    {
        $this->validComponent()
            ->set('districtCode', '1801')
            ->set('villageCode', '1801000001')
            ->call('register')
            ->assertHasErrors(['districtCode' => 'starts_with', 'villageCode' => 'starts_with']);
    }

    #[TestDox('PLG-REG-011 Kode kecamatan dan desa yang tidak terdaftar ditolak')]
    public function test_plg_reg_011_unknown_region_codes_are_rejected(): void
    {
        $response = $this->performRegistration('180499', '1804999999');
        $this->assertDatabaseMissing('users', ['email' => 'pelanggan.baru@example.test']);
        $this->assertNull($response);
    }

    #[TestDox('PLG-REG-012 Detail alamat terlalu pendek atau panjang ditolak')]
    public function test_plg_reg_012_address_boundaries_are_validated(): void
    {
        $this->validComponent()->set('detailAddress', 'Jl')->call('register')->assertHasErrors(['detailAddress' => 'min']);
        $this->validComponent()->set('detailAddress', str_repeat('A', 201))->call('register')->assertHasErrors(['detailAddress' => 'max']);
    }

    #[TestDox('PLG-REG-013 Pemilihan kecamatan memuat desa terkait dan mereset pilihan lama')]
    public function test_plg_reg_013_district_selection_loads_related_villages(): void
    {
        Livewire::test(Register::class)
            ->set('villageCode', '1804042001')
            ->set('districtCode', '180419')
            ->assertSet('villageCode', '')
            ->assertSet('villages.0.code', '1804192001');
    }

    #[TestDox('PLG-REG-014 Data valid membuat akun customer dan alamat lengkap')]
    public function test_plg_reg_014_valid_registration_creates_customer_account(): void
    {
        $response = $this->performRegistration('180419', '1804192001');

        $user = User::query()->where('email', 'pelanggan.baru@example.test')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertSame('180419', $user->district_code);
        $this->assertStringContainsString('SUMBER ALAM', $user->address);
        $this->assertStringContainsString('AIR HITAM', $user->address);
        $this->assertAuthenticatedAs($user);
        $this->assertSame(route('home'), $response->getTargetUrl());
    }

    private function validComponent(): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(Register::class)
            ->set('name', 'Pelanggan Baru')
            ->set('email', 'pelanggan.baru@example.test')
            ->set('phone', '081234567890')
            ->set('password', 'Password123')
            ->set('password_confirmation', 'Password123')
            ->set('terms', true)
            ->set('districtCode', '180419')
            ->set('villageCode', '1804192001')
            ->set('detailAddress', 'Jalan Mawar Nomor 10');
    }

    private function performRegistration(string $districtCode, string $villageCode): mixed
    {
        $component = Livewire::test(Register::class)->instance();
        $component->name = 'Pelanggan Baru';
        $component->email = 'pelanggan.baru@example.test';
        $component->phone = '081234567890';
        $component->password = 'Password123';
        $component->password_confirmation = 'Password123';
        $component->terms = true;
        $component->districtCode = $districtCode;
        $component->updatedDistrictCode($districtCode);
        $component->villageCode = $villageCode;
        $component->detailAddress = 'Jalan Mawar Nomor 10';

        $session = app('session')->driver();
        $session->start();
        $request = Request::create(route('register'), 'POST');
        $request->setLaravelSession($session);
        app()->instance('request', $request);

        return $component->register();
    }
}
