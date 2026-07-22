<?php

namespace Tests\Feature\BlackBox;

use App\Livewire\Auth\RegisterSeller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class SellerRegistrationBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createBlackBoxRegions();
    }

    #[TestDox('PJL-REG-001 Form registrasi penjual dapat dibuka')]
    public function test_pjl_reg_001_registration_page_is_public(): void
    {
        $this->get(route('register.seller'))->assertOk()->assertSee('Daftar Sebagai Penjual');
    }

    #[TestDox('PJL-REG-002 Seluruh field wajib registrasi penjual divalidasi')]
    public function test_pjl_reg_002_required_fields_are_validated(): void
    {
        Livewire::test(RegisterSeller::class)->call('register')->assertHasErrors([
            'store_name' => 'required', 'owner_name' => 'required', 'email' => 'required',
            'phone' => 'required', 'bank_code' => 'required', 'bank_account_no' => 'required',
            'bank_account_name' => 'required', 'password' => 'required', 'terms' => 'accepted',
            'districtCode' => 'required', 'villageCode' => 'required', 'detailAddress' => 'required',
        ]);
    }

    #[TestDox('PJL-REG-003 Format email penjual tidak valid ditolak')]
    public function test_pjl_reg_003_invalid_email_is_rejected(): void
    {
        $this->validComponent()->set('email', 'email-salah')->call('register')->assertHasErrors(['email' => 'email']);
    }

    #[TestDox('PJL-REG-004 Email penjual yang sudah digunakan ditolak')]
    public function test_pjl_reg_004_duplicate_email_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->validComponent()->set('email', $user->email)->call('register')->assertHasErrors(['email' => 'unique']);
    }

    #[TestDox('PJL-REG-005 Nomor telepon penjual lebih dari 15 karakter ditolak')]
    public function test_pjl_reg_005_phone_maximum_is_validated(): void
    {
        $this->validComponent()->set('phone', '0812345678901234')->call('register')->assertHasErrors(['phone' => 'max']);
    }

    #[TestDox('PJL-REG-006 Nomor telepon penjual berisi huruf ditolak')]
    public function test_pjl_reg_006_non_numeric_phone_is_rejected(): void
    {
        $this->validComponent()->set('phone', 'telepon-salah')->call('register')->assertHasErrors(['phone']);
    }

    #[TestDox('PJL-REG-007 Nomor rekening hanya boleh berisi angka')]
    public function test_pjl_reg_007_bank_account_number_must_be_numeric(): void
    {
        $this->validComponent()->set('bank_account_no', '123ABC')->call('register')->assertHasErrors(['bank_account_no' => 'regex']);
    }

    #[TestDox('PJL-REG-008 Nama pemilik rekening tidak menerima angka atau simbol berbahaya')]
    public function test_pjl_reg_008_bank_account_name_rejects_invalid_characters(): void
    {
        $this->validComponent()->set('bank_account_name', '<script>123</script>')->call('register')->assertHasErrors(['bank_account_name']);
    }

    #[TestDox('PJL-REG-009 Password penjual minimal delapan karakter')]
    public function test_pjl_reg_009_short_password_is_rejected(): void
    {
        $this->validComponent()->set('password', 'Pendek1')->call('register')->assertHasErrors(['password' => 'min']);
    }

    #[TestDox('PJL-REG-010 Persetujuan syarat penjual wajib dicentang')]
    public function test_pjl_reg_010_terms_must_be_accepted(): void
    {
        $this->validComponent()->set('terms', false)->call('register')->assertHasErrors(['terms' => 'accepted']);
    }

    #[TestDox('PJL-REG-011 Kode kecamatan yang tidak terdaftar ditolak')]
    public function test_pjl_reg_011_unknown_district_is_rejected(): void
    {
        $this->validComponent()->set('districtCode', '180499')->call('register')->assertHasErrors(['districtCode' => 'exists']);
    }

    #[TestDox('PJL-REG-012 Desa harus berada di kecamatan yang dipilih')]
    public function test_pjl_reg_012_village_must_belong_to_selected_district(): void
    {
        $response = $this->performRegistration('180419', '1804042001');
        $this->assertDatabaseMissing('users', ['email' => 'penjual.baru@example.test']);
        $this->assertNull($response);
    }

    #[TestDox('PJL-REG-013 Detail alamat toko mematuhi batas panjang')]
    public function test_pjl_reg_013_address_boundaries_are_validated(): void
    {
        $this->validComponent()->set('detailAddress', 'Jl')->call('register')->assertHasErrors(['detailAddress' => 'min']);
        $this->validComponent()->set('detailAddress', str_repeat('A', 201))->call('register')->assertHasErrors(['detailAddress' => 'max']);
    }

    #[TestDox('PJL-REG-014 Pemilihan kecamatan memuat desa terkait dan mereset pilihan lama')]
    public function test_pjl_reg_014_district_selection_loads_related_villages(): void
    {
        Livewire::test(RegisterSeller::class)
            ->set('villageCode', '1804042001')
            ->set('districtCode', '180419')
            ->assertSet('villageCode', '')
            ->assertSet('villages.0.code', '1804192001');
    }

    #[TestDox('PJL-REG-015 Data valid membuat akun seller dan toko berstatus pending')]
    public function test_pjl_reg_015_valid_registration_creates_pending_seller(): void
    {
        $response = $this->performRegistration('180419', '1804192001');
        $user = User::query()->where('email', 'penjual.baru@example.test')->firstOrFail();
        $store = Store::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('seller', $user->role);
        $this->assertTrue(Hash::check('Password123', $user->password));
        $this->assertSame('pending', $store->status);
        $this->assertSame('pending', $store->bank_verify_status);
        $this->assertStringContainsString('SUMBER ALAM', $store->address);
        $this->assertSame(route('login'), $response->getTargetUrl());
    }

    private function validComponent(): \Livewire\Features\SupportTesting\Testable
    {
        return Livewire::test(RegisterSeller::class)
            ->set('store_name', 'Toko Penjual Baru')
            ->set('owner_name', 'Pemilik Baru')
            ->set('email', 'penjual.baru@example.test')
            ->set('phone', '081234567890')
            ->set('description', 'Toko pengujian registrasi penjual')
            ->set('bank_code', 'BRI')
            ->set('bank_account_no', '1234567890')
            ->set('bank_account_name', 'PEMILIK BARU')
            ->set('password', 'Password123')
            ->set('terms', true)
            ->set('districtCode', '180419')
            ->set('villageCode', '1804192001')
            ->set('detailAddress', 'Jalan Mawar Nomor 10');
    }

    private function performRegistration(string $districtCode, string $villageCode): mixed
    {
        $component = Livewire::test(RegisterSeller::class)->instance();
        $component->store_name = 'Toko Penjual Baru';
        $component->owner_name = 'Pemilik Baru';
        $component->email = 'penjual.baru@example.test';
        $component->phone = '081234567890';
        $component->description = 'Toko pengujian registrasi penjual';
        $component->bank_code = 'BRI';
        $component->bank_account_no = '1234567890';
        $component->bank_account_name = 'PEMILIK BARU';
        $component->password = 'Password123';
        $component->terms = true;
        $component->districtCode = $districtCode;
        $component->updatedDistrictCode($districtCode);
        $component->villageCode = $villageCode;
        $component->detailAddress = 'Jalan Mawar Nomor 10';

        $session = app('session')->driver();
        $session->start();
        $request = Request::create(route('register.seller'), 'POST');
        $request->setLaravelSession($session);
        app()->instance('request', $request);

        return $component->register();
    }
}
