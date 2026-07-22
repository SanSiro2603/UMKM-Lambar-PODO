<?php

namespace App\Livewire\Admin;

use App\Models\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class Sellers extends Component
{
    public string $view = 'list'; // 'list' or 'show'

    public ?int $storeId = null;

    public string $statusFilter = 'semua';

    public string $search = '';

    public bool $showSuspendModal = false;

    public bool $showDeleteModal = false;

    public bool $showReactivateModal = false;

    public string $suspensionReason = '';

    public string $deleteReason = '';

    public string $deleteConfirmation = '';

    public function filterSellers(string $status)
    {
        $this->statusFilter = in_array($status, ['semua', 'pending', 'approved', 'rejected', 'suspended'], true)
            ? $status
            : 'semua';
    }

    public function showStore(int $id)
    {
        $this->storeId = $id;
        $this->view = 'show';
    }

    public function backToList()
    {
        $this->resetModerationForms();
        $this->storeId = null;
        $this->view = 'list';
    }

    public function approveStore()
    {
        $this->ensureAdmin();
        $store = Store::findOrFail($this->storeId);

        // 🔒 SECURITY FIX: Audit logging (ISSUE-013)
        Log::info('Store approved by admin', [
            'admin_id' => Auth::id(),
            'admin_email' => Auth::user()->email,
            'store_id' => $store->id,
            'store_name' => $store->name,
            'owner_id' => $store->user_id,
            'owner_email' => $store->user->email,
            'timestamp' => now(),
            'ip_address' => request()->ip(),
        ]);

        $store->update(['status' => 'approved']);

        $user = $store->user;
        $user->role = 'seller';
        $user->save();

        session()->flash('success', 'Toko '.$store->name.' telah disetujui.');
        $this->view = 'list';
        $this->storeId = null;
    }

    public function rejectStore()
    {
        $this->ensureAdmin();
        $store = Store::findOrFail($this->storeId);

        // 🔒 SECURITY FIX: Audit logging (ISSUE-013)
        Log::warning('Store rejected by admin', [
            'admin_id' => Auth::id(),
            'admin_email' => Auth::user()->email,
            'store_id' => $store->id,
            'store_name' => $store->name,
            'owner_id' => $store->user_id,
            'owner_email' => $store->user->email,
            'timestamp' => now(),
            'ip_address' => request()->ip(),
        ]);

        $store->update(['status' => 'rejected']);

        session()->flash('success', 'Toko '.$store->name.' telah ditolak.');
        $this->view = 'list';
        $this->storeId = null;
    }

    public function openSuspendModal(): void
    {
        $this->ensureAdmin();
        Store::query()->whereKey($this->storeId)->where('status', 'approved')->firstOrFail();
        $this->resetValidation();
        $this->suspensionReason = '';
        $this->showSuspendModal = true;
    }

    public function closeSuspendModal(): void
    {
        $this->resetValidation();
        $this->suspensionReason = '';
        $this->showSuspendModal = false;
    }

    public function suspendStore(): void
    {
        $this->ensureAdmin();
        $this->suspensionReason = trim($this->suspensionReason);
        $validated = $this->validate([
            'suspensionReason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'suspensionReason.required' => 'Alasan penonaktifan wajib diisi.',
            'suspensionReason.min' => 'Alasan penonaktifan minimal 5 karakter.',
            'suspensionReason.max' => 'Alasan penonaktifan maksimal 500 karakter.',
        ]);

        $admin = Auth::user();
        $ownerId = null;
        $audit = DB::transaction(function () use ($admin, $validated, &$ownerId): array {
            $store = Store::query()->with('user')->lockForUpdate()->findOrFail($this->storeId);
            abort_unless($store->status === 'approved', 409, 'Hanya toko aktif yang dapat dinonaktifkan.');

            $ownerId = $store->user_id;
            $store->update([
                'status' => 'suspended',
                'suspension_reason' => trim($validated['suspensionReason']),
                'suspended_at' => now(),
                'suspended_by' => $admin->id,
            ]);

            $this->deleteDatabaseSessions($ownerId);

            return [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'store_id' => $store->id,
                'store_name' => $store->name,
                'owner_id' => $ownerId,
                'owner_email' => $store->user->email,
                'reason' => trim($validated['suspensionReason']),
                'ip_address' => request()->ip(),
            ];
        });

        Log::warning('Seller suspended by admin', $audit);
        $this->closeSuspendModal();
        session()->flash('success', 'Seller berhasil dinonaktifkan dan akses loginnya telah diblokir.');
    }

    public function reactivateStore(): void
    {
        $this->ensureAdmin();
        $admin = Auth::user();

        $audit = DB::transaction(function () use ($admin): array {
            $store = Store::query()->with('user')->lockForUpdate()->findOrFail($this->storeId);
            abort_unless($store->status === 'suspended', 409, 'Hanya toko nonaktif yang dapat diaktifkan kembali.');

            $store->update(['status' => 'approved']);
            $store->user->forceFill(['role' => 'seller'])->save();

            return [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'store_id' => $store->id,
                'store_name' => $store->name,
                'owner_id' => $store->user_id,
                'owner_email' => $store->user->email,
                'previous_suspension_reason' => $store->suspension_reason,
                'ip_address' => request()->ip(),
            ];
        });

        Log::info('Seller reactivated by admin', $audit);
        $this->showReactivateModal = false;
        session()->flash('success', 'Seller berhasil diaktifkan kembali dan dapat login.');
    }

    public function openReactivateModal(): void
    {
        $this->ensureAdmin();
        Store::query()->whereKey($this->storeId)->where('status', 'suspended')->firstOrFail();
        $this->showReactivateModal = true;
    }

    public function closeReactivateModal(): void
    {
        $this->showReactivateModal = false;
    }

    public function openDeleteModal(): void
    {
        $this->ensureAdmin();
        Store::query()->findOrFail($this->storeId);
        $this->resetValidation();
        $this->deleteReason = '';
        $this->deleteConfirmation = '';
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->resetValidation();
        $this->deleteReason = '';
        $this->deleteConfirmation = '';
        $this->showDeleteModal = false;
    }

    public function deleteStorePermanently(): void
    {
        $this->ensureAdmin();
        $this->deleteReason = trim($this->deleteReason);
        $validated = $this->validate([
            'deleteReason' => ['required', 'string', 'min:5', 'max:500'],
            'deleteConfirmation' => ['required', 'string'],
        ], [
            'deleteReason.required' => 'Alasan penghapusan wajib diisi.',
            'deleteReason.min' => 'Alasan penghapusan minimal 5 karakter.',
            'deleteReason.max' => 'Alasan penghapusan maksimal 500 karakter.',
            'deleteConfirmation.required' => 'Ketik nama toko untuk mengonfirmasi penghapusan.',
        ]);

        $store = Store::query()
            ->with(['user', 'products:id,store_id,image', 'paymentMethods:id,store_id,qr_code'])
            ->withCount(['products', 'orders'])
            ->findOrFail($this->storeId);

        if (! hash_equals($store->name, trim($validated['deleteConfirmation']))) {
            $this->addError('deleteConfirmation', 'Nama toko tidak cocok. Ketik persis seperti yang ditampilkan.');

            return;
        }

        $files = collect([$store->logo, $store->banner, $store->ktp_photo])
            ->merge($store->products->pluck('image'))
            ->merge($store->paymentMethods->pluck('qr_code'))
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->unique()
            ->values();

        $snapshot = [
            'admin_id' => Auth::id(),
            'admin_email' => Auth::user()->email,
            'store_id' => $store->id,
            'store_name' => $store->name,
            'owner_id' => $store->user_id,
            'owner_email' => $store->user->email,
            'status' => $store->status,
            'products_count' => $store->products_count,
            'orders_count' => $store->orders_count,
            'transactions_count' => DB::table('transactions')->where('seller_id', $store->user_id)->count(),
            'reason' => trim($validated['deleteReason']),
            'ip_address' => request()->ip(),
        ];

        Log::warning('Seller permanent deletion initiated by admin', $snapshot);

        DB::transaction(function () use ($store): void {
            $this->deleteDatabaseSessions($store->user_id);
            $store->user->delete();
        });

        if ($files->isNotEmpty() && ! Storage::disk('public')->delete($files->all())) {
            Log::warning('Some seller files could not be deleted', [
                'store_id' => $snapshot['store_id'],
                'files' => $files->all(),
            ]);
        }

        Log::warning('Seller permanently deleted by admin', $snapshot);
        $this->resetModerationForms();
        $this->storeId = null;
        $this->view = 'list';
        session()->flash('success', 'Seller dan seluruh data terkait berhasil dihapus permanen.');
    }

    public function render()
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'admin') {
            abort(403);
        }

        if ($this->view === 'show' && $this->storeId) {
            $store = Store::with(['user', 'paymentMethods', 'suspendedBy'])
                ->withCount(['products', 'orders'])
                ->findOrFail($this->storeId);

            return view('livewire.admin.sellers', [
                'store' => $store,
            ]);
        }

        $query = Store::with('user');

        if (trim($this->search) !== '') {
            $term = trim($this->search);
            $query->where(function ($sellerQuery) use ($term): void {
                $sellerQuery->where('name', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($userQuery) use ($term): void {
                        $userQuery->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        if ($this->statusFilter !== 'semua') {
            $query->where('status', $this->statusFilter);
        }

        $stores = $query->orderBy('created_at', 'desc')->get();

        return view('livewire.admin.sellers', [
            'stores' => $stores,
        ]);
    }

    private function ensureAdmin(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
    }

    private function deleteDatabaseSessions(int $userId): void
    {
        if (Schema::hasTable(config('session.table', 'sessions'))) {
            DB::table(config('session.table', 'sessions'))->where('user_id', $userId)->delete();
        }
    }

    private function resetModerationForms(): void
    {
        $this->resetValidation();
        $this->showSuspendModal = false;
        $this->showDeleteModal = false;
        $this->showReactivateModal = false;
        $this->suspensionReason = '';
        $this->deleteReason = '';
        $this->deleteConfirmation = '';
    }
}
