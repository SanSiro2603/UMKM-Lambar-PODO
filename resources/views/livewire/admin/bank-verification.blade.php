<div>
    <div class="flex items-center justify-between mb-6">
        <h2 class="font-heading font-bold text-surface-900 text-xl">Verifikasi Rekening Bank</h2>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari toko atau pemilik..." class="w-64 px-3 py-2 border border-surface-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{!! session('success') !!}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    {{-- Reject Modal --}}
    @if($rejectingStoreId)
    <div class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="font-heading font-bold text-surface-900 mb-2">Tolak Rekening Bank</h3>
            <p class="text-sm text-surface-500 mb-4">Berikan alasan penolakan rekening seller ini.</p>
            <div class="mb-4">
                <label class="block text-sm font-medium text-surface-700 mb-1">Alasan Penolakan</label>
                <textarea wire:model="rejectReason" rows="3" class="w-full px-3 py-2 border border-surface-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-300" placeholder="Tulis alasan penolakan..."></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button wire:click="cancelReject" class="px-4 py-2 text-sm font-medium text-surface-600 bg-surface-100 rounded-xl hover:bg-surface-200 transition-colors">Batal</button>
                <button wire:click="reject" class="px-4 py-2 text-sm font-semibold text-white bg-red-500 rounded-xl hover:bg-red-600 transition-colors">
                    <span wire:loading.remove wire:target="reject">Tolak Rekening</span><span wire:loading wire:target="reject">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-50 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold text-surface-600">Nama Toko</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Pemilik</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Bank</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">No. Rekening</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Atas Nama</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Status</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-50">
                    @php
                        $sc = [
                            'unverified' => ['bg' => 'bg-surface-100', 'text' => 'text-surface-600', 'label' => 'Belum Terdaftar'],
                            'pending'    => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'label' => 'Menunggu'],
                            'verified'   => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'label' => 'Terverifikasi'],
                            'rejected'   => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'label' => 'Ditolak'],
                        ];
                    @endphp
                    @forelse($stores as $st)
                        @php $bs = $st->bank_verify_status ?? 'unverified'; $bc = $sc[$bs]; @endphp
                        <tr class="hover:bg-surface-50/50 transition-colors">
                            <td class="px-5 py-4"><div class="flex items-center gap-3"><div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center shrink-0"><span class="font-bold text-primary-600">{{ strtoupper(substr($st->name, 0, 1)) }}</span></div><span class="font-medium text-surface-800">{{ $st->name }}</span></div></td>
                            <td class="px-5 py-4 text-surface-600">{{ $st->user?->name ?? '-' }}</td>
                            <td class="px-5 py-4 text-surface-600">{{ $st->bank_code ?? '-' }}</td>
                            <td class="px-5 py-4 text-surface-600">{{ $st->bank_account_no ?? '-' }}</td>
                            <td class="px-5 py-4 text-surface-600">{{ $st->bank_account_name ?? '-' }}</td>
                            <td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $bc['bg'] }} {{ $bc['text'] }}">{{ $bc['label'] }}</span></td>
                            <td class="px-5 py-4">
                                @if($bs === 'pending')
                                <div class="flex items-center gap-2">
                                    <button wire:click="approve({{ $st->id }})" wire:confirm="Setujui rekening bank {{ $st->name }}?" class="px-3 py-1 text-xs font-semibold text-white bg-green-500 rounded-lg hover:bg-green-600 transition-colors"><span wire:loading.remove wire:target="approve({{ $st->id }})">Approve</span><span wire:loading wire:target="approve({{ $st->id }})">...</span></button>
                                    <button wire:click="showRejectModal({{ $st->id }})" class="px-3 py-1 text-xs font-semibold text-red-500 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors">Tolak</button>
                                </div>
                                @elseif($bs === 'verified')
                                    <span class="text-xs text-green-600 font-medium">Sudah diverifikasi</span>
                                @elseif($bs === 'rejected')
                                    <span class="text-xs text-red-500" title="{{ $st->bank_reject_reason }}">Ditolak: {{ Str::limit($st->bank_reject_reason, 30) }}</span>
                                @else
                                    <span class="text-xs text-surface-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-surface-500">Belum ada seller yang mendaftarkan rekening bank.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stores->hasPages())<div class="px-5 py-3 border-t border-surface-100">{{ $stores->links() }}</div>@endif
    </div>
</div>