@extends('layouts.dashboard')
@section('title', 'Verifikasi Seller')
@section('page-title', 'Verifikasi Seller')
@section('role-label', 'Admin')
@section('role-badge-class', 'bg-red-50 text-red-700')
@section('role-dot-class', 'bg-red-500')
@section('user-initial', 'A')
@section('user-name', 'Administrator')

@section('sidebar')
    <x-sidebar-link href="/admin/dashboard" label="Dashboard" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>' />
    <x-sidebar-link href="/admin/sellers" label="Verifikasi Seller" :active="true" badge="3" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35"/></svg>' />
    <x-sidebar-link href="/admin/categories" label="Kategori" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>' />
    <x-sidebar-link href="/admin/reports" label="Laporan Platform" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>' />
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-surface-50 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold text-surface-600">Nama Toko</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Pemilik</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Tanggal Daftar</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Status</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-50">
                    @php
                        $sellers = [
                            ['name' => 'Kue Tradisional Bu Emi', 'owner' => 'Emiliana', 'date' => '5 Jun 2025', 'status' => 'pending'],
                            ['name' => 'Herbal Nusantara', 'owner' => 'Drs. Hasan M.', 'date' => '4 Jun 2025', 'status' => 'pending'],
                            ['name' => 'Warung Kain Tenun', 'owner' => 'Kartini S.', 'date' => '3 Jun 2025', 'status' => 'pending'],
                            ['name' => 'Toko Kopi Pak Adi', 'owner' => 'Adi Suryanto', 'date' => '15 Jan 2025', 'status' => 'approved'],
                            ['name' => 'Snack Nusantara', 'owner' => 'Nuraini', 'date' => '20 Jan 2025', 'status' => 'approved'],
                            ['name' => 'Rotan Jaya', 'owner' => 'Jaya Kusuma', 'date' => '1 Feb 2025', 'status' => 'approved'],
                            ['name' => 'Toko Maju Bersama', 'owner' => 'Rudi H.', 'date' => '2 Jun 2025', 'status' => 'rejected'],
                        ];
                    @endphp
                    @foreach($sellers as $seller)
                        <tr class="hover:bg-surface-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center shrink-0">
                                        <span class="font-bold text-primary-600">{{ strtoupper(substr($seller['name'], 0, 1)) }}</span>
                                    </div>
                                    <span class="font-medium text-surface-800">{{ $seller['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-surface-600">{{ $seller['owner'] }}</td>
                            <td class="px-5 py-4 text-surface-500">{{ $seller['date'] }}</td>
                            <td class="px-5 py-4"><x-status-badge :status="$seller['status']" /></td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ url('/admin/sellers/1') }}" class="text-sm text-primary-500 hover:underline font-medium">Detail</a>
                                    @if($seller['status'] === 'pending')
                                        <button class="px-3 py-1 text-xs font-semibold text-white bg-green-500 rounded-lg hover:bg-green-600 transition-colors">Approve</button>
                                        <button class="px-3 py-1 text-xs font-semibold text-red-500 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors">Tolak</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
