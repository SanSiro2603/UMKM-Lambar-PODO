<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StorePaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->resetSeededTables();

        // 1. Admin user.
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890',
            'address' => 'Kantor Desa Air Hitam, Kec. Air Hitam, Kab. Lampung Barat',
        ]);

        // 2. Short product categories.
        $categories = [
            'agrokimia' => Category::create([
                'name' => 'Agrokimia',
                'slug' => 'agrokimia',
                'icon' => 'box',
            ]),
            'pupuk' => Category::create([
                'name' => 'Pupuk & Nutrisi',
                'slug' => 'pupuk-nutrisi',
                'icon' => 'box',
            ]),
            'benih' => Category::create([
                'name' => 'Benih & Bibit',
                'slug' => 'benih-bibit',
                'icon' => 'box',
            ]),
            'perlengkapan_tani' => Category::create([
                'name' => 'Perlengkapan Tani',
                'slug' => 'perlengkapan-tani',
                'icon' => 'box',
            ]),
            'alat_tani' => Category::create([
                'name' => 'Alat & Spare Part Tani',
                'slug' => 'alat-spare-part-tani',
                'icon' => 'box',
            ]),
            'kopi' => Category::create([
                'name' => 'Kopi',
                'slug' => 'kopi',
                'icon' => 'store',
            ]),
            'rempah' => Category::create([
                'name' => 'Rempah Perkebunan',
                'slug' => 'rempah-perkebunan',
                'icon' => 'store',
            ]),
        ];

        // 3. Sellers and stores from the provided seller data.
        $userAnton = User::create([
            'name' => 'Anton',
            'email' => 'anton@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'phone' => '081211112222',
            'address' => 'Desa Sri Menanti RT 02, Kec. Air Hitam, Kab. Lampung Barat',
        ]);

        $storeAnton = Store::create([
            'user_id' => $userAnton->id,
            'name' => 'Toko Anton',
            'slug' => 'toko-anton',
            'description' => 'Toko tani atau saprotan yang menjual obat-obatan pertanian, pupuk, benih, bibit tanaman, dan perlengkapan tani seperti polybag atau paranet.',
            'address' => 'Jl. Pendidikan RT 02, Sri Menanti, Air Hitam',
            'bank_name' => 'Bank Rakyat Indonesia (BRI)',
            'bank_code' => 'BRI',
            'bank_account_name' => 'Anton',
            'bank_account_no' => '1234-5678-9012',
            'bank_verify_status' => 'verified',
            'ktp_photo' => null,
            'status' => 'approved',
        ]);

        $userGunung = User::create([
            'name' => 'Gunung Tani',
            'email' => 'gunung@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'phone' => '081233334444',
            'address' => 'Desa Rigis Jaya RT 03, Kec. Air Hitam, Kab. Lampung Barat',
        ]);

        $storeGunung = Store::create([
            'user_id' => $userGunung->id,
            'name' => 'Gunung Tani',
            'slug' => 'gunung-tani',
            'description' => 'Toko perlengkapan pertanian yang menyediakan agrokimia, pupuk cair, benih saset, plastik mulsa atau polybag, alat tani kecil, dan spare part sprayer.',
            'address' => 'Jl. Raya Rigis Jaya No. 45, Air Hitam',
            'bank_name' => 'Bank Mandiri',
            'bank_code' => 'MANDIRI',
            'bank_account_name' => 'Gunung Tani',
            'bank_account_no' => '9876-5432-1098',
            'bank_verify_status' => 'verified',
            'ktp_photo' => null,
            'status' => 'approved',
        ]);

        $userRatna = User::create([
            'name' => 'Hj. Ratna Ningsih',
            'email' => 'rn@example.com',
            'password' => Hash::make('password'),
            'role' => 'seller',
            'phone' => '081255556666',
            'address' => 'Desa Semarang Jaya RT 05, Kec. Air Hitam, Kab. Lampung Barat',
        ]);

        $storeRatna = Store::create([
            'user_id' => $userRatna->id,
            'name' => 'Toko RN (Hj. Ratna Ningsih)',
            'slug' => 'toko-rn',
            'description' => 'Pengepul hasil bumi dan jual beli komoditas perkebunan seperti biji kopi kering, cengkeh, dan lada.',
            'address' => 'Jl. Merdeka No. 88, Semarang Jaya, Air Hitam',
            'bank_name' => 'Bank Negara Indonesia (BNI)',
            'bank_code' => 'BNI',
            'bank_account_name' => 'Hj. Ratna Ningsih',
            'bank_account_no' => '4567-8901-2345',
            'bank_verify_status' => 'verified',
            'ktp_photo' => null,
            'status' => 'approved',
        ]);

        // 4. Store payment methods.
        StorePaymentMethod::create([
            'store_id' => $storeAnton->id,
            'type' => 'bank',
            'name' => 'Bank Rakyat Indonesia (BRI)',
            'account_name' => 'Anton',
            'account_number' => '1234-5678-9012',
        ]);

        StorePaymentMethod::create([
            'store_id' => $storeAnton->id,
            'type' => 'gopay',
            'name' => 'GoPay',
            'account_name' => 'Anton',
            'account_number' => '081211112222',
        ]);

        StorePaymentMethod::create([
            'store_id' => $storeGunung->id,
            'type' => 'bank',
            'name' => 'Bank Mandiri',
            'account_name' => 'Gunung Tani',
            'account_number' => '9876-5432-1098',
        ]);

        StorePaymentMethod::create([
            'store_id' => $storeRatna->id,
            'type' => 'bank',
            'name' => 'Bank Negara Indonesia (BNI)',
            'account_name' => 'Hj. Ratna Ningsih',
            'account_number' => '4567-8901-2345',
        ]);

        // 5. Products from the provided seller product lists.
        foreach ($this->antonProducts() as $key => $product) {
            $categoryKey = $product['category'];
            unset($product['category']);

            Product::create([
                ...$product,
                'store_id' => $storeAnton->id,
                'category_id' => $categories[$categoryKey]->id,
            ]);
        }

        foreach ($this->gunungTaniProducts() as $key => $product) {
            $categoryKey = $product['category'];
            unset($product['category']);

            Product::create([
                ...$product,
                'store_id' => $storeGunung->id,
                'category_id' => $categories[$categoryKey]->id,
            ]);
        }

        foreach ($this->tokoRnProducts() as $key => $product) {
            $categoryKey = $product['category'];
            unset($product['category']);

            Product::create([
                ...$product,
                'store_id' => $storeRatna->id,
                'category_id' => $categories[$categoryKey]->id,
            ]);
        }

        // 6. Demo customer account without seeded transactions.
        User::create([
            'name' => 'Ahmad Supardi',
            'email' => 'customer@customer.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '081234567890',
            'address' => 'Pekon Rigis Jaya, Kec. Air Hitam, Kabupaten Lampung Barat',
        ]);
    }

    private function resetSeededTables(): void
    {
        Schema::disableForeignKeyConstraints();

        OrderItem::truncate();
        Order::truncate();
        StorePaymentMethod::truncate();
        Product::truncate();
        Category::truncate();
        Store::truncate();
        User::truncate();

        Schema::enableForeignKeyConstraints();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function antonProducts(): array
    {
        return [
            'anton_insektisida' => [
                'category' => 'agrokimia',
                'name' => 'Insektisida / Pestisida Pertanian 500ml',
                'slug' => 'insektisida-pestisida-pertanian-500ml',
                'description' => 'Obat-obatan pertanian untuk membantu mengendalikan hama tanaman seperti ulat, kutu daun, dan wereng.',
                'price' => 45000,
                'stock' => 50,
                'sold' => 0,
            ],
            'anton_herbisida' => [
                'category' => 'agrokimia',
                'name' => 'Herbisida Pembasmi Gulma 1L',
                'slug' => 'herbisida-pembasmi-gulma-1l',
                'description' => 'Herbisida untuk mengendalikan rumput liar atau gulma pada lahan pertanian dan perkebunan.',
                'price' => 60000,
                'stock' => 40,
                'sold' => 0,
            ],
            'anton_fungisida' => [
                'category' => 'agrokimia',
                'name' => 'Fungisida Anti Jamur Tanaman 250gr',
                'slug' => 'fungisida-anti-jamur-tanaman-250gr',
                'description' => 'Fungisida untuk membantu mencegah dan mengendalikan penyakit jamur pada daun, batang, dan buah.',
                'price' => 55000,
                'stock' => 35,
                'sold' => 0,
            ],
            'anton_pupuk_padat' => [
                'category' => 'pupuk',
                'name' => 'Pupuk Padat Karungan 5kg',
                'slug' => 'pupuk-padat-karungan-5kg',
                'description' => 'Pupuk padat karungan untuk kebutuhan pemupukan tanaman pertanian dan perkebunan.',
                'price' => 75000,
                'stock' => 30,
                'sold' => 0,
            ],
            'anton_pupuk_cair' => [
                'category' => 'pupuk',
                'name' => 'Pupuk Cair Nutrisi Tanaman 1L',
                'slug' => 'pupuk-cair-nutrisi-tanaman-1l',
                'description' => 'Pupuk cair atau nutrisi tanaman untuk menunjang pertumbuhan daun, bunga, dan buah.',
                'price' => 35000,
                'stock' => 60,
                'sold' => 0,
            ],
            'anton_benih' => [
                'category' => 'benih',
                'name' => 'Benih Tanaman Kemasan Saset',
                'slug' => 'benih-tanaman-kemasan-saset',
                'description' => 'Berbagai macam benih atau bibit tanaman dalam kemasan saset untuk kebutuhan kebun dan lahan tani.',
                'price' => 20000,
                'stock' => 100,
                'sold' => 0,
            ],
            'anton_polybag' => [
                'category' => 'perlengkapan_tani',
                'name' => 'Gulungan Plastik Polybag',
                'slug' => 'gulungan-plastik-polybag',
                'description' => 'Gulungan plastik untuk kebutuhan polybag tanaman dan pembibitan.',
                'price' => 30000,
                'stock' => 150,
                'sold' => 0,
            ],
            'anton_paranet' => [
                'category' => 'perlengkapan_tani',
                'name' => 'Gulungan Paranet Peneduh Tanaman',
                'slug' => 'gulungan-paranet-peneduh-tanaman',
                'description' => 'Paranet gulungan untuk membantu melindungi tanaman dan bibit dari paparan sinar matahari berlebih.',
                'price' => 150000,
                'stock' => 10,
                'sold' => 0,
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function gunungTaniProducts(): array
    {
        return [
            'gunung_insektisida' => [
                'category' => 'agrokimia',
                'name' => 'Insektisida Pertanian 500ml',
                'slug' => 'insektisida-pertanian-500ml',
                'description' => 'Insektisida untuk membantu mengendalikan hama tanaman pada lahan pertanian.',
                'price' => 48000,
                'stock' => 45,
                'sold' => 0,
            ],
            'gunung_herbisida' => [
                'category' => 'agrokimia',
                'name' => 'Herbisida Pertanian 1L',
                'slug' => 'herbisida-pertanian-1l',
                'description' => 'Herbisida untuk membersihkan gulma dan rumput liar di sekitar area tanam.',
                'price' => 65000,
                'stock' => 55,
                'sold' => 0,
            ],
            'gunung_fungisida' => [
                'category' => 'agrokimia',
                'name' => 'Fungisida Pertanian 500gr',
                'slug' => 'fungisida-pertanian-500gr',
                'description' => 'Fungisida untuk mencegah dan mengendalikan serangan jamur pada tanaman.',
                'price' => 58000,
                'stock' => 40,
                'sold' => 0,
            ],
            'gunung_pupuk_cair' => [
                'category' => 'pupuk',
                'name' => 'Pupuk Cair Pertanian 1L',
                'slug' => 'pupuk-cair-pertanian-1l',
                'description' => 'Pupuk cair untuk membantu mencukupi nutrisi tanaman dan mendukung pertumbuhan.',
                'price' => 40000,
                'stock' => 70,
                'sold' => 0,
            ],
            'gunung_benih' => [
                'category' => 'benih',
                'name' => 'Benih Tanaman Saset',
                'slug' => 'benih-tanaman-saset',
                'description' => 'Beragam benih tanaman dalam kemasan saset untuk petani dan pekebun.',
                'price' => 20000,
                'stock' => 90,
                'sold' => 0,
            ],
            'gunung_mulsa' => [
                'category' => 'perlengkapan_tani',
                'name' => 'Plastik Mulsa / Polybag Gulungan',
                'slug' => 'plastik-mulsa-polybag-gulungan',
                'description' => 'Gulungan plastik mulsa atau polybag untuk kebutuhan lahan pertanian dan pembibitan.',
                'price' => 190000,
                'stock' => 15,
                'sold' => 0,
            ],
            'gunung_alat_tani' => [
                'category' => 'alat_tani',
                'name' => 'Alat Pertanian Skala Kecil',
                'slug' => 'alat-pertanian-skala-kecil',
                'description' => 'Perlengkapan dan alat tani skala kecil untuk membantu pekerjaan harian di kebun.',
                'price' => 85000,
                'stock' => 25,
                'sold' => 0,
            ],
            'gunung_sprayer' => [
                'category' => 'alat_tani',
                'name' => 'Alat Semprot Sprayer Manual 16 Liter',
                'slug' => 'alat-semprot-sprayer-manual-16-liter',
                'description' => 'Sprayer manual untuk aplikasi pupuk cair, pestisida, herbisida, dan fungisida.',
                'price' => 250000,
                'stock' => 8,
                'sold' => 0,
            ],
            'gunung_spare_part' => [
                'category' => 'alat_tani',
                'name' => 'Spare Part Sprayer',
                'slug' => 'spare-part-sprayer',
                'description' => 'Suku cadang untuk alat semprot seperti nozzle, selang, dan komponen sprayer lainnya.',
                'price' => 25000,
                'stock' => 50,
                'sold' => 0,
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function tokoRnProducts(): array
    {
        return [
            'rn_biji_kopi' => [
                'category' => 'kopi',
                'name' => 'Biji Kopi Kering',
                'slug' => 'biji-kopi-kering',
                'description' => 'Biji kopi kering hasil bumi lokal yang siap dibeli atau dijual sebagai komoditas perkebunan.',
                'price' => 50000,
                'stock' => 1000,
                'sold' => 0,
            ],
            'rn_cengkeh' => [
                'category' => 'rempah',
                'name' => 'Cengkeh',
                'slug' => 'cengkeh',
                'description' => 'Cengkeh hasil perkebunan lokal untuk kebutuhan jual beli komoditas hasil bumi.',
                'price' => 120000,
                'stock' => 300,
                'sold' => 0,
            ],
            'rn_lada' => [
                'category' => 'rempah',
                'name' => 'Lada',
                'slug' => 'lada',
                'description' => 'Lada kering pilihan sebagai komoditas perkebunan yang dibeli dan dijual oleh pengepul.',
                'price' => 90000,
                'stock' => 400,
                'sold' => 0,
            ],
        ];
    }
}
