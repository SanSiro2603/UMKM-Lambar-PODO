<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class RegisterSeller extends Component
{
    public string $store_name = '';
    public string $owner_name = '';
    public string $email = '';
    public string $phone = '';
    public string $description = '';
    public string $bank_code = '';
    public string $bank_account_no = '';
    public string $bank_account_name = '';
    public string $password = '';
    public bool $terms = false;

    // Region: seller di Kabupaten Lampung Barat (city_code 1804)
    public string $districtCode = '';
    public string $villageCode = '';
    public string $detailAddress = '';

    public array $districts = [];
    public array $villages = [];

    protected array $rules = [
        'store_name' => 'required|string|max:255',
        'owner_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|max:15',
        'description' => 'nullable|string',
        'bank_code' => 'required|string|max:20',
        'bank_account_no' => 'required|string|max:30|regex:/^\d+$/',
        'bank_account_name' => 'required|string|max:100',
        'password' => 'required|string|min:8',
        'terms' => 'accepted',
        'districtCode' => 'required|string|exists:districts,code',
        'villageCode' => 'required|string|exists:villages,code',
        'detailAddress' => 'required|string|min:5|max:200',
    ];

    protected array $messages = [
        'terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan Penjual.',
        'password.min' => 'Password minimal 8 karakter.',
        'bank_account_no.regex' => 'Nomor rekening hanya boleh berisi angka.',
        'districtCode.required' => 'Silakan pilih kecamatan.',
        'districtCode.exists' => 'Kecamatan tidak valid.',
        'villageCode.required' => 'Silakan pilih desa/kelurahan.',
        'villageCode.exists' => 'Desa/kelurahan tidak valid.',
        'detailAddress.required' => 'Detail alamat toko wajib diisi.',
    ];

    public function mount(): void
    {
        $this->districts = District::where('city_code', '1804')
            ->orderBy('name')
            ->get()
            ->map(fn($d) => ['code' => $d->code, 'name' => $d->name])
            ->toArray();

        if ($this->districtCode) {
            $this->villages = Village::where('district_code', $this->districtCode)
                ->orderBy('name')
                ->get()
                ->map(fn($v) => ['code' => $v->code, 'name' => $v->name])
                ->toArray();
        }
    }

    public function updatedDistrictCode(string $value): void
    {
        $this->villageCode = '';
        $this->villages = [];

        if (! $value) {
            return;
        }

        $this->villages = Village::where('district_code', $value)
            ->orderBy('name')
            ->get()
            ->map(fn($v) => ['code' => $v->code, 'name' => $v->name])
            ->toArray();
    }

    private function getDistrictName(string $code): string
    {
        return collect($this->districts)->firstWhere('code', $code)['name'] ?? $code;
    }

    private function getVillageName(string $code): string
    {
        return collect($this->villages)->firstWhere('code', $code)['name'] ?? $code;
    }

    public function register()
    {
        $this->validate();

        $district = District::where('code', $this->districtCode)->where('city_code', '1804')->first();
        $village = Village::where('code', $this->villageCode)->where('district_code', $this->districtCode)->first();

        if (! $district || ! $village) {
            session()->flash('error', 'Data wilayah tidak valid.');
            return;
        }

        $fullAddress = $this->detailAddress
            . ', Desa/Kel. ' . $village->name
            . ', Kec. ' . $district->name
            . ', Kabupaten Lampung Barat';

        $user = User::create([
            'name' => $this->owner_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $fullAddress,
            'password' => Hash::make($this->password),
        ]);
        $user->forceFill(['role' => 'seller'])->save();

        Store::create([
            'user_id' => $user->id,
            'name' => $this->store_name,
            'slug' => Str::slug($this->store_name) . '-' . rand(100, 999),
            'description' => $this->description,
            'address' => $fullAddress,
            'bank_name' => $this->bank_code,
            'bank_code' => $this->bank_code,
            'bank_account_name' => $this->bank_account_name,
            'bank_account_no' => $this->bank_account_no,
            'bank_verify_status' => 'pending',
            'status' => 'pending',
        ]);

        session()->flash('success', 'Pendaftaran berhasil! Akun toko Anda sedang dalam peninjauan Admin (1x24 jam). Silakan masuk setelah verifikasi.');

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.auth.register-seller', [
            'districts' => $this->districts,
            'villages' => $this->villages,
        ])->extends('layouts.auth')->section('content');
    }
}
