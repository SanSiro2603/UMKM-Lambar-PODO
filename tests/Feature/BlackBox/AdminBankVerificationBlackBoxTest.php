<?php

namespace Tests\Feature\BlackBox;

use App\Livewire\Admin\BankVerification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class AdminBankVerificationBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    #[TestDox('ADM-BNK-001 Daftar rekening hanya memuat toko yang memiliki nomor rekening')]
    public function test_adm_bnk_001_list_only_shows_registered_accounts(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $registered = $this->bankStore(['name' => 'Toko Ada Rekening']);
        $missing = $this->makeBlackBoxStore('approved', ['name' => 'Toko Tanpa Rekening', 'bank_account_no' => null]);

        Livewire::actingAs($admin)->test(BankVerification::class)
            ->assertSee($registered->name)->assertDontSee($missing->name);
    }

    #[TestDox('ADM-BNK-002 Daftar rekening menampilkan data dan status verifikasi')]
    public function test_adm_bnk_002_list_shows_bank_data_and_status(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->bankStore(['bank_verify_status' => 'pending']);
        Livewire::actingAs($admin)->test(BankVerification::class)
            ->assertSee($store->bank_code)->assertSee($store->bank_account_no)
            ->assertSee($store->bank_account_name)->assertSee('Menunggu');
    }

    #[TestDox('ADM-BNK-003 Pencarian rekening berdasarkan nama toko')]
    public function test_adm_bnk_003_search_filters_by_store_name(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $wanted = $this->bankStore(['name' => 'Toko Kopi Pilihan']);
        $other = $this->bankStore(['name' => 'Toko Kerajinan']);
        Livewire::actingAs($admin)->test(BankVerification::class)->set('search', 'Kopi')
            ->assertSee($wanted->name)->assertDontSee($other->name);
    }

    #[TestDox('ADM-BNK-004 Pencarian rekening berdasarkan nama pemilik')]
    public function test_adm_bnk_004_search_filters_by_owner_name(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $wantedOwner = $this->makeBlackBoxUser('seller', ['name' => 'Pemilik Sangat Unik']);
        $wanted = $this->bankStore(['seller' => $wantedOwner, 'name' => 'Toko Pertama']);
        $other = $this->bankStore(['name' => 'Toko Kedua']);
        Livewire::actingAs($admin)->test(BankVerification::class)->set('search', 'Sangat Unik')
            ->assertSee($wanted->name)->assertDontSee($other->name);
    }

    #[TestDox('ADM-BNK-005 Pagination rekening membatasi 20 toko per halaman')]
    public function test_adm_bnk_005_accounts_are_paginated_by_twenty(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        for ($i = 1; $i <= 21; $i++) {
            $this->bankStore(['name' => sprintf('Toko Rekening %02d', $i)]);
        }
        Livewire::actingAs($admin)->test(BankVerification::class)
            ->assertViewHas('stores', fn ($stores) => $stores->count() === 20 && $stores->total() === 21);
    }

    #[TestDox('ADM-BNK-006 Persetujuan rekening mengubah status verified dan menghapus alasan penolakan')]
    public function test_adm_bnk_006_approve_verifies_account_and_clears_reason(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->bankStore(['bank_verify_status' => 'rejected', 'bank_reject_reason' => 'Alasan lama']);
        Livewire::actingAs($admin)->test(BankVerification::class)->call('approve', $store->id)->assertSee('berhasil diverifikasi');
        $this->assertDatabaseHas('stores', ['id' => $store->id, 'bank_verify_status' => 'verified', 'bank_reject_reason' => null]);
    }

    #[TestDox('ADM-BNK-007 Tombol tolak membuka modal dan mereset alasan lama')]
    public function test_adm_bnk_007_reject_modal_selects_store_and_resets_reason(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->bankStore();
        Livewire::actingAs($admin)->test(BankVerification::class)->set('rejectReason', 'Sementara')
            ->call('showRejectModal', $store->id)->assertSet('rejectingStoreId', $store->id)
            ->assertSet('rejectReason', '')->assertSee('Tolak Rekening Bank');
    }

    #[TestDox('ADM-BNK-008 Alasan kosong tidak boleh menolak rekening')]
    public function test_adm_bnk_008_empty_reject_reason_does_not_reject_account(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->bankStore(['bank_verify_status' => 'pending']);
        Livewire::actingAs($admin)->test(BankVerification::class)->call('showRejectModal', $store->id)
            ->set('rejectReason', '   ')->call('reject')->assertSee('Catatan wajib diisi');
        $this->assertSame('pending', $store->fresh()->bank_verify_status);
    }

    #[TestDox('ADM-BNK-009 Alasan valid menolak rekening dan menyimpan catatan')]
    public function test_adm_bnk_009_valid_reason_rejects_account(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->bankStore(['bank_verify_status' => 'pending']);
        Livewire::actingAs($admin)->test(BankVerification::class)->call('showRejectModal', $store->id)
            ->set('rejectReason', 'Nama pemilik tidak sesuai')->call('reject')
            ->assertSet('rejectingStoreId', null)->assertSet('rejectReason', '');
        $this->assertDatabaseHas('stores', [
            'id' => $store->id, 'bank_verify_status' => 'rejected',
            'bank_reject_reason' => 'Nama pemilik tidak sesuai',
        ]);
    }

    #[TestDox('ADM-BNK-010 Batal menolak membersihkan modal dan alasan')]
    public function test_adm_bnk_010_cancel_reject_clears_modal(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->bankStore();
        Livewire::actingAs($admin)->test(BankVerification::class)->call('showRejectModal', $store->id)
            ->set('rejectReason', 'Tidak jadi')->call('cancelReject')
            ->assertSet('rejectingStoreId', null)->assertSet('rejectReason', '');
    }

    #[TestDox('ADM-BNK-011 ID toko tidak valid pada penolakan menghasilkan 404')]
    public function test_adm_bnk_011_unknown_rejecting_store_is_not_found(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($admin)->test(BankVerification::class)
            ->set('rejectingStoreId', 999999)->set('rejectReason', 'Data tidak sesuai')->call('reject');
    }

    private function bankStore(array $attributes = []): \App\Models\Store
    {
        return $this->makeBlackBoxStore('approved', array_merge([
            'bank_name' => 'BRI',
            'bank_code' => 'BRI',
            'bank_account_no' => (string) random_int(1000000000, 1999999999),
            'bank_account_name' => 'PEMILIK TOKO',
            'bank_verify_status' => 'pending',
        ], $attributes));
    }
}
