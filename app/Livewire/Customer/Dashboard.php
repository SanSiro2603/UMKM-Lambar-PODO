<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\Order;
use App\Services\ActiveOrderShippingSyncService;
use Illuminate\Support\Facades\Auth;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public string $detailAddress = '';
    public string $kelurahan = '';
    public string $kecamatan = 'Air Hitam';
    public string $selectedDistrictCode = '180419'; // default Air Hitam
    public string $selectedVillageCode = '';
    public bool $isEditingAddress = false;

    public function mount()
    {
        $this->parseAddress();
    }

    public function parseAddress()
    {
        $user = Auth::user();
        if (!$user) return;

        $userAddress = $user->address;
        if ($userAddress) {
            // Try to extract from formatted address
            if (preg_match('/^(.*?),\s*(?:Desa\/Kel\.|Desa|Kel\.)\s+(.*?),\s*(?:Kec\.|Kecamatan)\s+(.*?),\s*Kabupaten\s+Lampung\s+Barat/i', $userAddress, $matches)) {
                $this->detailAddress = trim($matches[1]);
                $kelurahanName = trim($matches[2]);
                $kecamatanName = trim($matches[3]);

                $this->kecamatan = $kecamatanName;

                // Find district code from DB name
                $district = District::query()->where('city_code', '1804')
                    ->where('name', 'like', '%' . strtoupper($kecamatanName) . '%')
                    ->first();
                if ($district) {
                    $this->selectedDistrictCode = $district->code;

                    // Find village
                    $village = Village::query()->where('district_code', $district->code)
                        ->where('name', 'like', '%' . strtoupper($kelurahanName) . '%')
                        ->first();
                    if ($village) {
                        $this->selectedVillageCode = $village->code;
                        $this->kelurahan = $village->name;
                    } else {
                        $this->kelurahan = $kelurahanName;
                    }
                } else {
                    $this->kelurahan = $kelurahanName;
                }
            } else {
                $this->detailAddress = $userAddress;
                $this->kelurahan = '';
                $this->selectedDistrictCode = '180419';
                $this->kecamatan = 'Air Hitam';
            }
        } else {
            $this->detailAddress = '';
            $this->kelurahan = '';
            $this->selectedDistrictCode = '180419';
            $this->kecamatan = 'Air Hitam';
        }
    }

    public function editAddress()
    {
        $this->isEditingAddress = true;
    }

    public function cancelEdit()
    {
        $this->isEditingAddress = false;
        $this->parseAddress();
    }

    /** Ketika kecamatan dipilih, load desa */
    public function updatedSelectedDistrictCode(string $value): void
    {
        $this->selectedVillageCode = '';
        $this->kelurahan = '';
    }

    /** Preview dampak ongkir saat kecamatan baru dipilih. */
    public function getShippingPreviewProperty(): array
    {
        if (! $this->isEditingAddress || ! $this->selectedDistrictCode || ! Auth::user()) {
            return ['orders' => [], 'skipped' => 0];
        }

        return app(ActiveOrderShippingSyncService::class)
            ->preview(Auth::user(), $this->selectedDistrictCode);
    }

    /** Dapatkan daftar kecamatan Lampung Barat */
    public function getDistrictsProperty(): array
    {
        return District::query()->where('city_code', '1804')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /** Dapatkan daftar desa berdasarkan kecamatan terpilih */
    public function getVillagesProperty(): array
    {
        if (!$this->selectedDistrictCode) return [];
        return Village::query()->where('district_code', $this->selectedDistrictCode)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function updateAddress(ActiveOrderShippingSyncService $shippingSync)
    {
        $this->validate([
            'selectedDistrictCode' => 'required|string|starts_with:1804',
            'selectedVillageCode' => 'required|string|starts_with:1804',
            'detailAddress' => 'required|string|min:5|max:500',
        ], [
            'detailAddress.required' => 'Detail alamat (jalan, RT/RW, no rumah) wajib diisi.',
            'detailAddress.min' => 'Detail alamat harus diisi minimal 5 karakter.',
            'selectedDistrictCode.required' => 'Silakan pilih kecamatan.',
            'selectedVillageCode.required' => 'Silakan pilih desa/kelurahan.',
        ]);

        $district = District::query()
            ->where('code', $this->selectedDistrictCode)
            ->where('city_code', '1804')
            ->first();
        $village = Village::query()
            ->where('code', $this->selectedVillageCode)
            ->where('district_code', $this->selectedDistrictCode)
            ->first();

        if (!$district || !$village) {
            session()->flash('error', 'Data wilayah tidak valid.');
            return;
        }

        $fullAddress = $this->detailAddress
            . ', Desa/Kel. ' . $village->name
            . ', Kec. ' . $district->name
            . ', Kabupaten Lampung Barat';

        $user = Auth::user();
        $user->update([
            'address' => $fullAddress,
            'district_code' => $this->selectedDistrictCode,
        ]);

        $syncResult = $shippingSync->sync(
            $user->fresh(),
            $fullAddress,
            $this->selectedDistrictCode,
        );

        $this->kecamatan = $district->name;
        $this->kelurahan = $village->name;

        $successMessage = 'Alamat pengiriman berhasil diperbarui.';
        if ($syncResult['updated'] > 0) {
            $successMessage .= " {$syncResult['updated']} pesanan aktif telah disesuaikan.";
        }
        if ($syncResult['invoice_resets'] > 0) {
            $successMessage .= " {$syncResult['invoice_resets']} invoice lama dibatalkan; buka detail pesanan untuk membuat invoice baru.";
        }
        session()->flash('success', $successMessage);

        $warnings = [];
        if ($syncResult['skipped'] > 0) {
            $warnings[] = "{$syncResult['skipped']} pesanan yang sudah lunas atau dikirim tetap menggunakan alamat lama.";
        }
        if ($syncResult['failed'] > 0) {
            $warnings[] = implode(' ', $syncResult['failures']);
        }
        if ($warnings !== []) {
            session()->flash('shipping-sync-warning', implode(' ', $warnings));
        }

        $this->isEditingAddress = false;
    }

    #[On('order-shipping-updated')]
    public function refreshOrders(?int $orderId = null): void
    {
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            abort(403);
        }

        $pesananAktif = Order::query()->where('customer_id', $user->id)
            ->whereIn('status', ['waiting_payment', 'processing', 'shipped'])
            ->count();

        $pesananSelesai = Order::query()->where('customer_id', $user->id)
            ->where('status', 'delivered')
            ->count();

        $totalBelanja = Order::query()->where('customer_id', $user->id)
            ->whereIn('status', ['processing', 'shipped', 'delivered'])
            ->sum('total_price');

        $menungguPembayaran = Order::query()->where('customer_id', $user->id)
            ->where('status', 'waiting_payment')
            ->where('payment_method', 'xendit')
            ->count();

        $recentOrders = Order::with('store')
            ->where('customer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.customer.dashboard', [
            'user' => $user,
            'pesananAktif' => $pesananAktif,
            'pesananSelesai' => $pesananSelesai,
            'totalBelanja' => $totalBelanja,
            'menungguPembayaran' => $menungguPembayaran,
            'recentOrders' => $recentOrders,
        ]);
    }
}
