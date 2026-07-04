<?php

namespace App\Livewire\Seller;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreProfile extends Component
{
    use WithFileUploads;

    public Store $store;

    public string $name = '';
    public string $description = '';
    public string $address = '';
    
    public $newLogo;
    public $newBanner;

    public function mount()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'seller' || !$user->store) {
            abort(403);
        }

        $this->store = $user->store;
        $this->name = $this->store->name;
        $this->description = $this->store->description ?? '';
        $this->address = $this->store->address ?? '';
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|min:3|max:100',
            'description' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'newLogo' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp', // 🔒 SECURITY FIX (ISSUE-004)
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=2048,max_height=2048'
            ],
            'newBanner' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp', // 🔒 SECURITY FIX (ISSUE-004)
                'max:2048',
                'dimensions:min_width=800,min_height=300,max_width=4096,max_height=2048'
            ],
        ], [
            'name.required' => 'Nama toko wajib diisi.',
            'name.min' => 'Nama toko minimal 3 karakter.',
            'newLogo.image' => 'File logo harus berupa gambar.',
            'newLogo.mimes' => 'Logo harus berformat JPEG, PNG, atau WebP.',
            'newLogo.max' => 'Ukuran logo maksimal 2MB.',
            'newBanner.image' => 'File banner harus berupa gambar.',
            'newBanner.mimes' => 'Banner harus berformat JPEG, PNG, atau WebP.',
            'newBanner.max' => 'Ukuran banner maksimal 2MB.',
        ]);

        try {
            // 🔒 SECURITY FIX: Sanitize description (ISSUE-015)
            $cleanDescription = strip_tags($this->description, '<p><br><b><i><u><strong><em>');
            
            $updateData = [
                'name' => $this->name,
                'description' => $cleanDescription,
                'address' => $this->address,
            ];

            // Regenerate slug dynamically if the name changed
            if ($this->name !== $this->store->name) {
                $slug = Str::slug($this->name);
                if (Store::where('slug', $slug)->where('id', '!=', $this->store->id)->exists()) {
                    $slug = $slug . '-' . $this->store->id;
                }
                $updateData['slug'] = $slug;
            }

            // Upload logo if updated — store first, then compress at final location
            if ($this->newLogo) {
                if ($this->store->logo) {
                    Storage::disk('public')->delete($this->store->logo);
                }
                
                // 🔒 SECURITY FIX: Secure filename (ISSUE-004)
                $extension = $this->newLogo->getClientOriginalExtension();
                $logoFilename = 'logo-' . Str::uuid() . '.' . $extension;
                $logoPath = $this->newLogo->storeAs('stores/logos', $logoFilename, 'public');
                
                if ($logoPath) {
                    // Verify MIME type
                    $mimeType = Storage::disk('public')->mimeType($logoPath);
                    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
                        Storage::disk('public')->delete($logoPath);
                        throw new \Exception('Invalid logo file type');
                    }
                    
                    // Compress at the final storage location
                    $fullPath = Storage::disk('public')->path($logoPath);
                    \App\Helpers\ImageCompressor::compressPath($fullPath);
                    $updateData['logo'] = $logoPath;
                }
            }

            // Upload banner if updated — store first, then compress at final location
            if ($this->newBanner) {
                if ($this->store->banner) {
                    Storage::disk('public')->delete($this->store->banner);
                }
                
                // 🔒 SECURITY FIX: Secure filename (ISSUE-004)
                $extension = $this->newBanner->getClientOriginalExtension();
                $bannerFilename = 'banner-' . Str::uuid() . '.' . $extension;
                $bannerPath = $this->newBanner->storeAs('stores/banners', $bannerFilename, 'public');
                
                if ($bannerPath) {
                    // Verify MIME type
                    $mimeType = Storage::disk('public')->mimeType($bannerPath);
                    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
                        Storage::disk('public')->delete($bannerPath);
                        throw new \Exception('Invalid banner file type');
                    }
                    
                    // Compress at the final storage location
                    $fullPath = Storage::disk('public')->path($bannerPath);
                    \App\Helpers\ImageCompressor::compressPath($fullPath);
                    $updateData['banner'] = $bannerPath;
                }
            }

            $this->store->update($updateData);
            $this->store->refresh();

            $this->newLogo = null;
            $this->newBanner = null;

            session()->flash('success', 'Profil toko berhasil diperbarui.');
        } catch (\Exception $e) {
            // 🔒 SECURITY FIX: Don't expose error details (ISSUE-005)
            Log::error('Store profile update failed', [
                'user_id' => Auth::id(),
                'store_id' => $this->store->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Gagal menyimpan profil. Silakan coba lagi atau hubungi admin.');
        }
    }

    public function render()
    {
        return view('livewire.seller.store-profile')
            ->extends('layouts.dashboard')
            ->section('content');
    }
}
