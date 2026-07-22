<?php

namespace App\Livewire;

use App\Models\Store;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class StoreDetail extends Component
{
    public string $slug;

    public bool $showContactModal = false;

    public string $contactTopic = '';

    public string $contactNote = '';

    private const CONTACT_TOPICS = [
        'product' => 'Tanya produk',
        'stock_price' => 'Stok dan harga',
        'order_payment' => 'Pemesanan dan pembayaran',
        'shipping' => 'Pengiriman',
        'complaint' => 'Keluhan/komplain',
    ];

    public function mount(string $slug)
    {
        $this->slug = $slug;
    }

    public function openContactModal(): void
    {
        $store = $this->approvedStore();

        if (! $this->formatWhatsappNumber($store->user?->phone)) {
            $this->addError('whatsapp', 'Nomor WhatsApp toko belum tersedia.');

            return;
        }

        $this->resetValidation();
        $this->contactTopic = '';
        $this->contactNote = '';
        $this->showContactModal = true;
    }

    public function closeContactModal(): void
    {
        $this->resetValidation();
        $this->contactTopic = '';
        $this->contactNote = '';
        $this->showContactModal = false;
    }

    public function contactSeller(): void
    {
        $validated = $this->validate([
            'contactTopic' => ['required', 'string', 'in:'.implode(',', array_keys(self::CONTACT_TOPICS))],
            'contactNote' => ['nullable', 'string', 'max:500'],
        ], [
            'contactTopic.required' => 'Pilih topik pesan terlebih dahulu.',
            'contactTopic.in' => 'Topik pesan tidak valid.',
            'contactNote.max' => 'Catatan tambahan maksimal 500 karakter.',
        ]);

        $store = $this->approvedStore();
        $number = $this->formatWhatsappNumber($store->user?->phone);

        if (! $number) {
            $this->addError('whatsapp', 'Nomor WhatsApp toko belum tersedia atau tidak valid.');

            return;
        }

        $topic = self::CONTACT_TOPICS[$validated['contactTopic']];
        $message = "Halo {$store->name},\n\nSaya ingin menghubungi toko mengenai: {$topic}.";

        if ($validated['contactTopic'] === 'complaint') {
            $message .= "\nMohon bantu menindaklanjuti keluhan saya. Nomor pesanan akan saya sertakan jika tersedia.";
        }

        $note = trim($validated['contactNote'] ?? '');
        if ($note !== '') {
            $message .= "\n\nCatatan:\n{$note}";
        }

        $message .= "\n\nHalaman toko: ".route('stores.show', $store->slug);
        $url = "https://wa.me/{$number}?text=".rawurlencode($message);

        $this->showContactModal = false;
        $this->dispatch('open-whatsapp', url: $url);
    }

    public function render()
    {
        $store = $this->approvedStore();
        $products = $store->products()
            ->with('category')
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->withSoldQuantity()
            ->latest()
            ->get();

        return view('livewire.store-detail', [
            'store' => $store,
            'products' => $products,
            'contactTopics' => self::CONTACT_TOPICS,
            'whatsappAvailable' => $this->formatWhatsappNumber($store->user?->phone) !== null,
        ]);
    }

    private function approvedStore(): Store
    {
        return Store::query()
            ->with('user')
            ->where('slug', $this->slug)
            ->where('status', 'approved')
            ->firstOrFail();
    }

    private function formatWhatsappNumber(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', $phone ?? '');

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return preg_match('/^628[1-9][0-9]{6,11}$/', $digits) === 1 ? $digits : null;
    }
}
