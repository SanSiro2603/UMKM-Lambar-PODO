<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    // Region fields (locked to Lampung Barat)
    public string $districtCode = '';
    public string $villageCode = '';
    public string $detailAddress = '';

    // Untuk dropdown cascading
    public array $districts = [];
    public array $villages = [];

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|max:15',
        'password' => 'required|string|min:8|confirmed',
        'terms' => 'accepted',
        'districtCode' => 'required|string|starts_with:1804',
        'villageCode' => 'required|string|starts_with:1804',
        'detailAddress' => 'required|string|min:5|max:200',
    ];

    protected array $messages = [
        'terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan.',
        'password.min' => 'Password minimal 8 karakter.',
        'districtCode.required' => 'Silakan pilih kecamatan.',
        'villageCode.required' => 'Silakan pilih desa/kelurahan.',
        'detailAddress.required' => 'Detail alamat wajib diisi.',
        'detailAddress.min' => 'Detail alamat minimal 5 karakter.',
    ];

    public function mount(): void
    {
        // Load kecamatan Lampung Barat (code 1804)
        $this->districts = District::where('city_code', '1804')
            ->orderBy('name')
            ->get()
            ->map(fn($d) => ['code' => $d->code, 'name' => $d->name])
            ->toArray();
    }

    /** Ketika kecamatan dipilih, load desa/kelurahan */
    public function updatedDistrictCode(string $value): void
    {
        $this->villageCode = '';
        $this->villages = [];
        if ($value) {
            $this->villages = Village::where('district_code', $value)
                ->orderBy('name')
                ->get()
                ->map(fn($v) => ['code' => $v->code, 'name' => $v->name])
                ->toArray();
        }
    }

    /** Ambil nama dari kode */
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

        // Format alamat: Detail, Desa/Kel. X, Kec. X, Kabupaten Lampung Barat
        $fullAddress = $this->detailAddress
            . ', Desa/Kel. ' . $this->getVillageName($this->villageCode)
            . ', Kec. ' . $this->getDistrictName($this->districtCode)
            . ', Kabupaten Lampung Barat';

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $fullAddress,
            'district_code' => $this->districtCode,
            'password' => Hash::make($this->password),
            'role' => 'customer',
        ]);

        Auth::login($user);

        session()->regenerate();

        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.auth.register', [
            'districts' => $this->districts,
            'villages' => $this->villages,
        ])->extends('layouts.auth')->section('content');
    }
}
