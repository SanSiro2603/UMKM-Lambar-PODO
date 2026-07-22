<?php

namespace Tests\Feature\BlackBox;

use App\Livewire\Admin\Categories;
use App\Livewire\Admin\Sellers;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class AdminSellersCategoriesBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    #[TestDox('ADM-SEL-001 Daftar penjual menampilkan seluruh status toko')]
    public function test_adm_sel_001_seller_list_shows_all_store_statuses(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $pending = $this->makeBlackBoxStore('pending', ['name' => 'Toko Pending Admin']);
        $approved = $this->makeBlackBoxStore('approved', ['name' => 'Toko Approved Admin']);
        $rejected = $this->makeBlackBoxStore('rejected', ['name' => 'Toko Rejected Admin']);

        Livewire::actingAs($admin)->test(Sellers::class)
            ->assertSee($pending->name)->assertSee($approved->name)->assertSee($rejected->name);
    }

    #[TestDox('ADM-SEL-002 Filter status penjual hanya menampilkan status terpilih')]
    public function test_adm_sel_002_status_filter_limits_stores(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $pending = $this->makeBlackBoxStore('pending', ['name' => 'Hanya Pending']);
        $approved = $this->makeBlackBoxStore('approved', ['name' => 'Bukan Pending']);

        Livewire::actingAs($admin)->test(Sellers::class)->call('filterSellers', 'pending')
            ->assertSet('statusFilter', 'pending')->assertSee($pending->name)->assertDontSee($approved->name);
    }

    #[TestDox('ADM-SEL-003 Halaman penjual menyediakan pencarian toko atau pemilik')]
    public function test_adm_sel_003_seller_page_provides_search_control(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $this->actingAs($admin)->get(route('admin.sellers'))->assertOk()->assertSee('Cari toko atau pemilik');
    }

    #[TestDox('ADM-SEL-004 Detail penjual menampilkan identitas toko dan pemilik')]
    public function test_adm_sel_004_store_detail_shows_store_and_owner(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->makeBlackBoxStore('pending', ['name' => 'Toko Detail Admin', 'description' => 'Deskripsi detail admin']);

        Livewire::actingAs($admin)->test(Sellers::class)->call('showStore', $store->id)
            ->assertSet('view', 'show')->assertSet('storeId', $store->id)
            ->assertSee($store->name)->assertSee($store->user->name)->assertSee($store->user->email);
    }

    #[TestDox('ADM-SEL-005 Detail penjual menampilkan nomor rekening tersimpan')]
    public function test_adm_sel_005_store_detail_shows_bank_account_number(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->makeBlackBoxStore('pending', [
            'bank_name' => 'BRI', 'bank_code' => 'BRI',
            'bank_account_no' => '999988887777', 'bank_account_name' => 'PEMILIK TOKO',
        ]);

        Livewire::actingAs($admin)->test(Sellers::class)->call('showStore', $store->id)->assertSee('999988887777');
    }

    #[TestDox('ADM-SEL-006 ID toko tidak valid pada detail menghasilkan 404')]
    public function test_adm_sel_006_unknown_store_detail_is_not_found(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($admin)->test(Sellers::class)->call('showStore', 999999);
    }

    #[TestDox('ADM-SEL-007 Kembali dari detail mereset toko terpilih')]
    public function test_adm_sel_007_back_to_list_resets_detail_state(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->makeBlackBoxStore('pending');
        Livewire::actingAs($admin)->test(Sellers::class)->call('showStore', $store->id)->call('backToList')
            ->assertSet('view', 'list')->assertSet('storeId', null);
    }

    #[TestDox('ADM-SEL-008 Persetujuan detail toko mengubah status dan peran pemilik')]
    public function test_adm_sel_008_approving_store_updates_status_and_owner_role(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->makeBlackBoxStore('pending');
        $store->user->forceFill(['role' => 'customer'])->save();

        Livewire::actingAs($admin)->test(Sellers::class)->call('showStore', $store->id)
            ->call('approveStore')->assertSet('view', 'list')->assertSet('storeId', null);
        $this->assertSame('approved', $store->fresh()->status);
        $this->assertSame('seller', $store->user->fresh()->role);
    }

    #[TestDox('ADM-SEL-009 Penolakan detail toko mengubah status menjadi rejected')]
    public function test_adm_sel_009_rejecting_store_updates_status(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->makeBlackBoxStore('pending');
        Livewire::actingAs($admin)->test(Sellers::class)->call('showStore', $store->id)->call('rejectStore');
        $this->assertSame('rejected', $store->fresh()->status);
    }

    #[TestDox('ADM-CAT-001 Daftar kategori ditampilkan berurutan berdasarkan nama')]
    public function test_adm_cat_001_categories_are_sorted_by_name(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        Category::create(['name' => 'Zaitun', 'slug' => 'zaitun']);
        Category::create(['name' => 'Agro', 'slug' => 'agro']);
        Livewire::actingAs($admin)->test(Categories::class)->assertSeeInOrder(['Agro', 'Zaitun']);
    }

    #[TestDox('ADM-CAT-002 Pencarian kategori memfilter berdasarkan nama')]
    public function test_adm_cat_002_search_filters_category_name(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        Category::create(['name' => 'Kopi Lokal', 'slug' => 'kopi-lokal']);
        Category::create(['name' => 'Kerajinan', 'slug' => 'kerajinan']);
        Livewire::actingAs($admin)->test(Categories::class)->set('search', 'Kopi')
            ->assertSee('Kopi Lokal')->assertDontSee('Kerajinan');
    }

    #[TestDox('ADM-CAT-003 Tombol tambah dan batal mereset formulir kategori')]
    public function test_adm_cat_003_create_and_cancel_reset_form(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        Livewire::actingAs($admin)->test(Categories::class)->call('showCreateForm')->assertSet('showForm', true)
            ->set('name', 'Sementara')->call('cancel')->assertSet('showForm', false)->assertSet('name', '')->assertSet('categoryId', null);
    }

    #[TestDox('ADM-CAT-004 Nama kategori kosong ditolak')]
    public function test_adm_cat_004_category_name_is_required(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        Livewire::actingAs($admin)->test(Categories::class)->call('saveCategory')->assertHasErrors(['name' => 'required']);
    }

    #[TestDox('ADM-CAT-005 Nama kategori mematuhi batas 3 sampai 50 karakter')]
    public function test_adm_cat_005_category_name_boundaries_are_validated(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        Livewire::actingAs($admin)->test(Categories::class)->set('name', 'AB')->call('saveCategory')->assertHasErrors(['name' => 'min']);
        Livewire::actingAs($admin)->test(Categories::class)->set('name', str_repeat('A', 51))->call('saveCategory')->assertHasErrors(['name' => 'max']);
    }

    #[TestDox('ADM-CAT-006 Nama kategori duplikat ditolak')]
    public function test_adm_cat_006_duplicate_category_name_is_rejected(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        Category::create(['name' => 'Kategori Sama', 'slug' => 'kategori-sama']);
        Livewire::actingAs($admin)->test(Categories::class)->set('name', 'Kategori Sama')
            ->call('saveCategory')->assertHasErrors(['name' => 'unique']);
    }

    #[TestDox('ADM-CAT-007 Kategori valid dibuat beserta slug')]
    public function test_adm_cat_007_valid_category_is_created_with_slug(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        Livewire::actingAs($admin)->test(Categories::class)->set('name', 'Produk Olahan Baru')
            ->call('saveCategory')->assertHasNoErrors()->assertSet('showForm', false);
        $this->assertDatabaseHas('categories', ['name' => 'Produk Olahan Baru', 'slug' => 'produk-olahan-baru']);
    }

    #[TestDox('ADM-CAT-008 Form edit memuat kategori terpilih')]
    public function test_adm_cat_008_edit_form_loads_category(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $category = Category::create(['name' => 'Kategori Lama', 'slug' => 'kategori-lama']);
        Livewire::actingAs($admin)->test(Categories::class)->call('editCategory', $category->id)
            ->assertSet('categoryId', $category->id)->assertSet('name', 'Kategori Lama')->assertSet('showForm', true);
    }

    #[TestDox('ADM-CAT-009 Kategori dapat diperbarui dan slug ikut berubah')]
    public function test_adm_cat_009_category_can_be_updated(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $category = Category::create(['name' => 'Kategori Lama', 'slug' => 'kategori-lama']);
        Livewire::actingAs($admin)->test(Categories::class)->call('editCategory', $category->id)
            ->set('name', 'Kategori Baru')->call('saveCategory')->assertHasNoErrors();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Kategori Baru', 'slug' => 'kategori-baru']);
    }

    #[TestDox('ADM-CAT-010 Edit kategori tidak boleh menduplikasi kategori lain')]
    public function test_adm_cat_010_edit_cannot_duplicate_another_category(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $first = Category::create(['name' => 'Kategori Pertama', 'slug' => 'kategori-pertama']);
        $second = Category::create(['name' => 'Kategori Kedua', 'slug' => 'kategori-kedua']);
        Livewire::actingAs($admin)->test(Categories::class)->call('editCategory', $second->id)
            ->set('name', $first->name)->call('saveCategory')->assertHasErrors(['name' => 'unique']);
    }

    #[TestDox('ADM-CAT-011 Kategori kosong dapat dihapus')]
    public function test_adm_cat_011_empty_category_can_be_deleted(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $category = Category::create(['name' => 'Kategori Kosong', 'slug' => 'kategori-kosong']);
        Livewire::actingAs($admin)->test(Categories::class)->call('deleteCategory', $category->id);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    #[TestDox('ADM-CAT-012 Kategori yang masih digunakan produk tidak dapat dihapus')]
    public function test_adm_cat_012_used_category_cannot_be_deleted(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $product = $this->makeBlackBoxProduct();
        Livewire::actingAs($admin)->test(Categories::class)->call('deleteCategory', $product->category_id)
            ->assertSee('tidak dapat dihapus');
        $this->assertDatabaseHas('categories', ['id' => $product->category_id]);
    }

    #[TestDox('ADM-CAT-013 ID kategori tidak valid menghasilkan 404')]
    public function test_adm_cat_013_invalid_category_id_is_not_found(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($admin)->test(Categories::class)->call('editCategory', 999999);
    }
}
