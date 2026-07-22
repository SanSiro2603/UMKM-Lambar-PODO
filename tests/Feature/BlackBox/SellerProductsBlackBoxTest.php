<?php

namespace Tests\Feature\BlackBox;

use App\Livewire\Seller\Products;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class SellerProductsBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    #[TestDox('PJL-PRD-001 Daftar produk hanya menampilkan produk toko seller aktif')]
    public function test_pjl_prd_001_product_list_is_scoped_to_store(): void
    {
        $store = $this->makeBlackBoxStore();
        $own = $this->makeBlackBoxProduct($store, ['name' => 'Produk Milik Sendiri']);
        $other = $this->makeBlackBoxProduct(null, ['name' => 'Produk Toko Lain']);

        Livewire::actingAs($store->user)->test(Products::class)
            ->assertSee($own->name)->assertDontSee($other->name);
    }

    #[TestDox('PJL-PRD-002 Pencarian produk memfilter berdasarkan nama')]
    public function test_pjl_prd_002_search_filters_product_name(): void
    {
        $store = $this->makeBlackBoxStore();
        $this->makeBlackBoxProduct($store, ['name' => 'Kopi Robusta Unik']);
        $this->makeBlackBoxProduct($store, ['name' => 'Keripik Pisang']);

        Livewire::actingAs($store->user)->test(Products::class)->set('search', 'Robusta')
            ->assertSee('Kopi Robusta Unik')->assertDontSee('Keripik Pisang');
    }

    #[TestDox('PJL-PRD-003 Pagination produk membatasi sepuluh baris per halaman')]
    public function test_pjl_prd_003_products_are_paginated_by_ten(): void
    {
        $store = $this->makeBlackBoxStore();
        for ($i = 1; $i <= 11; $i++) {
            $this->makeBlackBoxProduct($store, ['name' => sprintf('Produk %02d', $i)]);
        }

        Livewire::actingAs($store->user)->test(Products::class)
            ->assertViewHas('products', fn ($products) => $products->count() === 10 && $products->total() === 11);
    }

    #[TestDox('PJL-PRD-004 Tombol tambah dan batal mengubah tampilan formulir')]
    public function test_pjl_prd_004_create_and_cancel_switch_views(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(Products::class)
            ->call('showCreateForm')->assertSet('view', 'create')
            ->set('name', 'Sementara')->call('cancel')->assertSet('view', 'list')->assertSet('name', '');
    }

    #[TestDox('PJL-PRD-005 Seluruh field wajib produk baru divalidasi')]
    public function test_pjl_prd_005_required_product_fields_are_validated(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(Products::class)->call('saveProduct')->assertHasErrors([
            'name' => 'required', 'description' => 'required', 'category_id' => 'required',
            'price' => 'required', 'stock' => 'required', 'image' => 'required',
        ]);
    }

    #[TestDox('PJL-PRD-006 Nama produk mematuhi batas 3 sampai 100 karakter')]
    public function test_pjl_prd_006_product_name_boundaries_are_validated(): void
    {
        $this->validComponent()->set('name', 'AB')->call('saveProduct')->assertHasErrors(['name' => 'min']);
        $this->validComponent()->set('name', str_repeat('N', 101))->call('saveProduct')->assertHasErrors(['name' => 'max']);
    }

    #[TestDox('PJL-PRD-007 Deskripsi produk mematuhi batas 10 sampai 5000 karakter')]
    public function test_pjl_prd_007_description_boundaries_are_validated(): void
    {
        $this->validComponent()->set('description', 'Pendek')->call('saveProduct')->assertHasErrors(['description' => 'min']);
        $this->validComponent()->set('description', str_repeat('D', 5001))->call('saveProduct')->assertHasErrors(['description' => 'max']);
    }

    #[TestDox('PJL-PRD-008 Kategori produk harus terdaftar')]
    public function test_pjl_prd_008_category_must_exist(): void
    {
        $this->validComponent()->set('category_id', '999999')->call('saveProduct')->assertHasErrors(['category_id' => 'exists']);
    }

    #[TestDox('PJL-PRD-009 Harga produk harus berupa bilangan bulat numerik')]
    public function test_pjl_prd_009_price_must_be_integer_numeric(): void
    {
        $this->validComponent()->set('price', 'harga')->call('saveProduct')->assertHasErrors(['price' => 'numeric']);
        $this->validComponent()->set('price', '100.50')->call('saveProduct')->assertHasErrors(['price' => 'regex']);
    }

    #[TestDox('PJL-PRD-010 Harga produk mematuhi batas Rp100 sampai Rp999.999.999')]
    public function test_pjl_prd_010_price_boundaries_are_validated(): void
    {
        $this->validComponent()->set('price', '99')->call('saveProduct')->assertHasErrors(['price' => 'min']);
        $this->validComponent()->set('price', '1000000000')->call('saveProduct')->assertHasErrors(['price' => 'max']);
    }

    #[TestDox('PJL-PRD-011 Stok produk harus berupa bilangan bulat')]
    public function test_pjl_prd_011_stock_must_be_integer(): void
    {
        $this->validComponent()->set('stock', '1.5')->call('saveProduct')->assertHasErrors(['stock' => 'integer']);
        $this->validComponent()->set('stock', 'stok')->call('saveProduct')->assertHasErrors(['stock' => 'integer']);
    }

    #[TestDox('PJL-PRD-012 Stok produk mematuhi batas 0 sampai 999.999')]
    public function test_pjl_prd_012_stock_boundaries_are_validated(): void
    {
        $this->validComponent()->set('stock', '-1')->call('saveProduct')->assertHasErrors(['stock' => 'min']);
        $this->validComponent()->set('stock', '1000000')->call('saveProduct')->assertHasErrors(['stock' => 'max']);
    }

    #[TestDox('PJL-PRD-013 File produk non-gambar ditolak')]
    public function test_pjl_prd_013_non_image_file_is_rejected(): void
    {
        $this->validComponent(UploadedFile::fake()->create('produk.pdf', 100, 'application/pdf'))
            ->call('saveProduct')->assertHasErrors(['image' => 'image']);
    }

    #[TestDox('PJL-PRD-014 Format gambar produk dibatasi JPEG PNG dan WebP')]
    public function test_pjl_prd_014_image_extension_is_whitelisted(): void
    {
        $this->validComponent(UploadedFile::fake()->image('produk.gif', 400, 400))
            ->call('saveProduct')->assertHasErrors(['image' => 'mimes']);
    }

    #[TestDox('PJL-PRD-015 Ukuran gambar produk maksimal dua megabita')]
    public function test_pjl_prd_015_image_size_is_limited(): void
    {
        $this->validComponent(UploadedFile::fake()->image('produk.jpg', 400, 400)->size(2049))
            ->call('saveProduct')->assertHasErrors(['image' => 'max']);
    }

    #[TestDox('PJL-PRD-016 Dimensi gambar produk minimum dan maksimum divalidasi')]
    public function test_pjl_prd_016_image_dimensions_are_validated(): void
    {
        $this->validComponent(UploadedFile::fake()->image('kecil.jpg', 299, 299))
            ->call('saveProduct')->assertHasErrors(['image' => 'dimensions']);
        $this->validComponent(UploadedFile::fake()->image('besar.jpg', 4097, 4097))
            ->call('saveProduct')->assertHasErrors(['image' => 'dimensions']);
    }

    #[TestDox('PJL-PRD-017 Data produk valid membuat produk pada toko aktif')]
    public function test_pjl_prd_017_valid_product_is_created(): void
    {
        Storage::fake('public');
        $component = $this->validComponent(UploadedFile::fake()->image('produk.png', 400, 400));
        $component->call('saveProduct')->assertHasNoErrors()->assertSet('view', 'list');

        $product = \App\Models\Product::query()->where('name', 'Produk Baru Valid')->firstOrFail();
        $this->assertSame(auth()->user()->store->id, $product->store_id);
        $this->assertSame(10000, (int) $product->price);
        $this->assertSame(5, $product->stock);
        Storage::disk('public')->assertExists($product->image);
    }

    #[TestDox('PJL-PRD-018 Seller pending tidak dapat menyimpan produk meskipun data valid')]
    public function test_pjl_prd_018_pending_store_cannot_save_product(): void
    {
        Storage::fake('public');
        $store = $this->makeBlackBoxStore('pending');
        $category = Category::create(['name' => 'Kategori Pending', 'slug' => 'kategori-pending', 'icon' => 'store']);
        Livewire::actingAs($store->user)->test(Products::class)
            ->set('name', 'Produk Pending')->set('description', 'Deskripsi produk pending')
            ->set('category_id', (string) $category->id)->set('price', '10000')->set('stock', '5')
            ->set('image', UploadedFile::fake()->image('pending.png', 400, 400))->call('saveProduct');

        $this->assertDatabaseMissing('products', ['name' => 'Produk Pending']);
    }

    #[TestDox('PJL-PRD-019 Form edit memuat produk milik seller')]
    public function test_pjl_prd_019_edit_form_loads_owned_product(): void
    {
        $store = $this->makeBlackBoxStore();
        $product = $this->makeBlackBoxProduct($store, ['name' => 'Produk Akan Diedit']);
        Livewire::actingAs($store->user)->test(Products::class)->call('showEditForm', $product->id)
            ->assertSet('view', 'edit')->assertSet('productId', $product->id)->assertSet('name', 'Produk Akan Diedit');
    }

    #[TestDox('PJL-PRD-020 Seller tidak dapat membuka produk toko lain untuk diedit')]
    public function test_pjl_prd_020_other_store_product_cannot_be_edited(): void
    {
        $store = $this->makeBlackBoxStore();
        $other = $this->makeBlackBoxProduct();
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($store->user)->test(Products::class)->call('showEditForm', $other->id);
    }

    #[TestDox('PJL-PRD-021 Produk milik seller dapat diperbarui tanpa mengganti gambar')]
    public function test_pjl_prd_021_owned_product_can_be_updated_without_new_image(): void
    {
        $store = $this->makeBlackBoxStore();
        $product = $this->makeBlackBoxProduct($store, ['name' => 'Nama Lama', 'image' => 'products/lama.png']);
        Livewire::actingAs($store->user)->test(Products::class)->call('showEditForm', $product->id)
            ->set('name', 'Nama Produk Baru')->set('description', 'Deskripsi produk telah diperbarui')
            ->set('price', '25000')->set('stock', '7')->call('saveProduct')->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Nama Produk Baru', 'price' => 25000, 'stock' => 7, 'image' => 'products/lama.png']);
    }

    #[TestDox('PJL-PRD-022 Mengganti gambar menghapus gambar lama produk')]
    public function test_pjl_prd_022_replacing_image_deletes_old_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/lama.png', 'old');
        $store = $this->makeBlackBoxStore();
        $product = $this->makeBlackBoxProduct($store, ['image' => 'products/lama.png']);
        Livewire::actingAs($store->user)->test(Products::class)->call('showEditForm', $product->id)
            ->set('image', UploadedFile::fake()->image('baru.png', 400, 400))->call('saveProduct')->assertHasNoErrors();

        Storage::disk('public')->assertMissing('products/lama.png');
        Storage::disk('public')->assertExists($product->fresh()->image);
    }

    #[TestDox('PJL-PRD-023 Produk milik seller dapat dihapus beserta gambarnya')]
    public function test_pjl_prd_023_owned_product_and_image_can_be_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('products/hapus.png', 'image');
        $store = $this->makeBlackBoxStore();
        $product = $this->makeBlackBoxProduct($store, ['image' => 'products/hapus.png']);
        Livewire::actingAs($store->user)->test(Products::class)->call('deleteProduct', $product->id);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing('products/hapus.png');
    }

    #[TestDox('PJL-PRD-024 Seller tidak dapat menghapus produk toko lain')]
    public function test_pjl_prd_024_other_store_product_cannot_be_deleted(): void
    {
        $store = $this->makeBlackBoxStore();
        $other = $this->makeBlackBoxProduct();
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($store->user)->test(Products::class)->call('deleteProduct', $other->id);
    }

    private function validComponent(?UploadedFile $image = null): \Livewire\Features\SupportTesting\Testable
    {
        Storage::fake('public');
        $store = $this->makeBlackBoxStore();
        $category = Category::create([
            'name' => 'Kategori Produk Baru '.uniqid(),
            'slug' => 'kategori-produk-baru-'.uniqid(),
            'icon' => 'store',
        ]);

        return Livewire::actingAs($store->user)->test(Products::class)
            ->set('name', 'Produk Baru Valid')
            ->set('description', 'Deskripsi produk baru yang valid')
            ->set('category_id', (string) $category->id)
            ->set('price', '10000')
            ->set('stock', '5')
            ->set('image', $image ?? UploadedFile::fake()->image('produk.jpg', 400, 400));
    }
}
