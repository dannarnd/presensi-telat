<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    cancellationLogs: Object,
    filters: Object,
    stats: Object,
});

// Search & Date Filters
const search = ref(props.filters.search || '');
const date = ref(props.filters.date || '');

const handleFilter = () => {
    const url = (typeof route === 'function' && route().has('admin.cancellation_logs.index'))
        ? route('admin.cancellation_logs.index')
        : '/admin/cancellation-logs';

    router.get(url, {
        search: search.value || undefined,
        date: date.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    date.value = '';
    handleFilter();
};

// Restore Modal
const showRestoreModal = ref(false);
const logToRestore = ref(null);
const restoreForm = useForm({});

const openRestoreModal = (log) => {
    logToRestore.value = log;
    showRestoreModal.value = true;
};

const submitRestore = () => {
    if (!logToRestore.value) return;
    const url = (typeof route === 'function' && route().has('admin.cancellation_logs.restore'))
        ? route('admin.cancellation_logs.restore', logToRestore.value.id)
        : `/admin/cancellation-logs/${logToRestore.value.id}/restore`;

    restoreForm.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            showRestoreModal.value = false;
            logToRestore.value = null;
        }
    });
};

// Force Delete Modal
const showForceDeleteModal = ref(false);
const logToForceDelete = ref(null);
const forceDeleteForm = useForm({});

const openForceDeleteModal = (log) => {
    logToForceDelete.value = log;
    showForceDeleteModal.value = true;
};

const submitForceDelete = () => {
    if (!logToForceDelete.value) return;
    const url = (typeof route === 'function' && route().has('admin.cancellation_logs.force_delete'))
        ? route('admin.cancellation_logs.force_delete', logToForceDelete.value.id)
        : `/admin/cancellation-logs/${logToForceDelete.value.id}/force`;

    forceDeleteForm.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            showForceDeleteModal.value = false;
            logToForceDelete.value = null;
        }
    });
};

// Raw UA View Modal
const showUaModal = ref(false);
const selectedUa = ref('');
const openUaModal = (ua) => {
    selectedUa.value = ua;
    showUaModal.value = true;
};

// Helpers
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const isMobileDevice = (deviceStr) => {
    if (!deviceStr) return false;
    return deviceStr.includes('📱') || deviceStr.toLowerCase().includes('android') || deviceStr.toLowerCase().includes('iphone');
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black bg-rose-100 text-rose-700 uppercase tracking-wider">
                            Keamanan Forensik
                        </span>
                        <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">Log Audit Pembatalan</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Pusat investigasi rekam jejak penghapusan presensi, identifikasi perangkat (HP/Laptop), dan pemulihan data siswa.
                    </p>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto space-y-6">

            <!-- Flash Success Message -->
            <div v-if="$page.props.flash.success" class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between animate-fade-in">
                <div class="flex items-center space-x-3">
                    <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-emerald-800 text-sm font-semibold">{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <!-- Forensic Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pembatalan</div>
                        <div class="text-2xl font-black text-slate-800">{{ stats?.total ?? 0 }} <span class="text-xs font-semibold text-slate-400 font-normal">kasus</span></div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Dibatalkan Hari Ini</div>
                        <div class="text-2xl font-black text-rose-600">{{ stats?.today ?? 0 }} <span class="text-xs font-semibold text-slate-400 font-normal">kasus</span></div>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl p-5 shadow-sm border border-amber-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-200">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-amber-900 uppercase tracking-wider">Deteksi Smartphone</div>
                        <p class="text-xs text-amber-700 leading-relaxed font-medium">
                            Baris bertanda <span class="font-bold text-rose-700">HP</span> mengindikasikan penghapusan lewat ponsel siswa, bukan laptop piket.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto flex-1">
                    <div class="relative w-full md:w-80">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            v-model="search"
                            @keyup.enter="handleFilter"
                            placeholder="Cari siswa, petugas, alasan, HP, IP..."
                            class="pl-9 pr-4 py-2 w-full text-xs rounded-xl border-slate-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm"
                        />
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <input
                            type="date"
                            v-model="date"
                            @change="handleFilter"
                            class="py-2 px-3 text-xs rounded-xl border-slate-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm text-slate-700"
                        />
                        <button
                            @click="handleFilter"
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold shadow-sm transition-colors"
                        >
                            Filter
                        </button>
                        <button
                            v-if="search || date"
                            @click="resetFilter"
                            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <div class="text-xs text-slate-500 font-medium">
                    Menampilkan <strong>{{ cancellationLogs?.data?.length ?? 0 }}</strong> dari <strong>{{ cancellationLogs?.total ?? 0 }}</strong> data
                </div>
            </div>

            <!-- Audit Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 divide-y divide-slate-100">
                        <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Siswa (Target Hapus)</th>
                                <th class="py-3.5 px-4">Waktu Batal & Dicatat</th>
                                <th class="py-3.5 px-4">Petugas / Pelaku</th>
                                <th class="py-3.5 px-4">Alasan Pembatalan</th>
                                <th class="py-3.5 px-4">Jejak Perangkat & IP</th>
                                <th class="py-3.5 px-4 text-center">Aksi Pemulihan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-normal">
                            <tr v-if="!cancellationLogs?.data || cancellationLogs.data.length === 0">
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </div>
                                        <span class="font-semibold text-slate-600">Tidak ada log pembatalan ditemukan.</span>
                                        <span class="text-[11px] text-slate-400">Semua data keterlambatan tersimpan aman tanpa ada pembatalan anomali.</span>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="log in cancellationLogs?.data"
                                :key="log.id"
                                :class="[
                                    'hover:bg-slate-50/70 transition-colors',
                                    isMobileDevice(log.deletion_device) ? 'bg-rose-50/30' : ''
                                ]"
                            >
                                <!-- Student Info -->
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-bold text-slate-800 text-sm leading-snug">
                                        {{ log.student?.name || 'Siswa Dihapus' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                        Kelas: <span class="font-semibold text-blue-600">{{ log.student?.school_class?.name || '-' }}</span>
                                        <span class="mx-1 text-slate-300">•</span>
                                        NISN: <span class="font-mono text-slate-600">{{ log.student?.nisn || '-' }}</span>
                                    </div>
                                </td>

                                <!-- Times -->
                                <td class="py-3.5 px-4 align-top space-y-1">
                                    <div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                            Dibatalkan:
                                        </span>
                                        <div class="font-bold text-slate-800 mt-0.5">{{ formatDate(log.deleted_at) }}</div>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        Dicatat: {{ formatDate(log.created_at) }}
                                    </div>
                                </td>

                                <!-- Deleter vs Reporter -->
                                <td class="py-3.5 px-4 align-top space-y-1">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Petugas Pembatal:</span>
                                        <div class="font-bold text-rose-600">{{ log.deleter_name || '(Tidak tercatat)' }}</div>
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        Pelapor Awal: <span class="font-medium text-slate-700">{{ log.reporter_name || 'Guru Piket' }}</span>
                                    </div>
                                    <div v-if="log.deleted_by_user" class="text-[10px] text-slate-400">
                                        Akun: {{ log.deleted_by_user.name }}
                                    </div>
                                </td>

                                <!-- Reason -->
                                <td class="py-3.5 px-4 align-top max-w-xs">
                                    <div class="bg-slate-100/80 rounded-lg p-2.5 text-slate-700 text-xs border border-slate-200/60 leading-relaxed font-medium">
                                        "{{ log.deletion_reason || '-' }}"
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1">
                                        Alasan Awal Terlambat: <span class="italic text-slate-500">{{ log.reason }}</span>
                                    </div>
                                </td>

                                <!-- Forensic Device Info -->
                                <td class="py-3.5 px-4 align-top space-y-1.5">
                                    <!-- Device Badge -->
                                    <div
                                        :class="[
                                            'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border shadow-xs',
                                            isMobileDevice(log.deletion_device)
                                                ? 'bg-rose-100 text-rose-800 border-rose-200 animate-pulse'
                                                : 'bg-slate-100 text-slate-700 border-slate-200'
                                        ]"
                                    >
                                        <span>{{ log.deletion_device || 'Perangkat Tidak Dikenal' }}</span>
                                    </div>

                                    <!-- IP & UA buttons -->
                                    <div class="flex items-center space-x-2 text-[11px] text-slate-500">
                                        <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-[10px] font-semibold text-slate-600">
                                            IP: {{ log.deletion_ip || '-' }}
                                        </span>
                                        <button
                                            v-if="log.deletion_user_agent"
                                            @click="openUaModal(log.deletion_user_agent)"
                                            class="text-blue-600 hover:text-blue-800 hover:underline text-[10px] font-semibold"
                                        >
                                            Detail Raw UA
                                        </button>
                                    </div>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3.5 px-4 align-top text-center">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <!-- Restore Button -->
                                        <button
                                            @click="openRestoreModal(log)"
                                            title="Pulihkan Keterlambatan Siswa"
                                            class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-sm transition-all flex items-center space-x-1 active:scale-95"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            <span>Pulihkan</span>
                                        </button>

                                        <!-- Permanent Delete Button -->
                                        <button
                                            @click="openForceDeleteModal(log)"
                                            title="Hapus Permanen Dari Database"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="cancellationLogs?.links && cancellationLogs.links.length > 3" class="bg-slate-50 px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Halaman <strong>{{ cancellationLogs.current_page }}</strong> dari <strong>{{ cancellationLogs.last_page }}</strong>
                    </div>
                    <div class="flex space-x-1">
                        <Link
                            v-for="(link, i) in cancellationLogs.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 text-xs rounded-lg transition-colors',
                                link.active ? 'bg-slate-800 text-white font-bold' : 'bg-white text-slate-600 hover:bg-slate-200 border border-slate-200',
                                !link.url ? 'opacity-40 pointer-events-none' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Restore Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showRestoreModal" class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 text-center mb-1">Pulihkan Log Keterlambatan?</h3>
                    <p class="text-xs text-slate-500 text-center mb-5">
                        Log keterlambatan atas nama <strong class="text-slate-800">{{ logToRestore?.student?.name }}</strong> akan dikembalikan aktif ke sistem presensi dan kembali masuk ke radar Guru BK.
                    </p>

                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 mb-5 text-xs text-slate-600 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Kelas:</span>
                            <span class="font-bold text-slate-700">{{ logToRestore?.student?.school_class?.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Alasan Pembatalan Sebelumnya:</span>
                            <span class="font-medium text-slate-700 italic">"{{ logToRestore?.deletion_reason }}"</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Perangkat Pembatal:</span>
                            <span class="font-bold text-rose-600">{{ logToRestore?.deletion_device }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="showRestoreModal = false"
                            class="px-4 py-2 text-slate-600 hover:text-slate-800 font-semibold bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors text-xs"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submitRestore"
                            :disabled="restoreForm.processing"
                            class="bg-emerald-600 text-white px-5 py-2 rounded-xl font-bold text-xs shadow-md shadow-emerald-200 hover:bg-emerald-700 disabled:opacity-50 transition-all flex items-center space-x-1"
                        >
                            <svg v-if="restoreForm.processing" class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>{{ restoreForm.processing ? 'Memulihkan...' : 'Ya, Pulihkan Data' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Force Delete Modal -->
        <Teleport to="body">
            <div v-if="showForceDeleteModal" class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl p-6 text-center">
                    <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-1">Musnahkan Permanen?</h3>
                    <p class="text-xs text-slate-500 mb-6">
                        Log atas nama <strong class="text-slate-800">{{ logToForceDelete?.student?.name }}</strong> akan dihapus selamanya dari database dan tidak dapat dipulihkan lagi.
                    </p>

                    <div class="flex justify-center space-x-3">
                        <button
                            type="button"
                            @click="showForceDeleteModal = false"
                            class="px-4 py-2 text-slate-600 hover:text-slate-800 font-semibold bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors text-xs"
                        >
                            Batal
                        </button>
                        <button
                            type="button"
                            @click="submitForceDelete"
                            :disabled="forceDeleteForm.processing"
                            class="bg-rose-600 text-white px-5 py-2 rounded-xl font-bold text-xs shadow-md shadow-rose-200 hover:bg-rose-700 disabled:opacity-50 transition-all"
                        >
                            {{ forceDeleteForm.processing ? 'Menghapus...' : 'Ya, Musnahkan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Raw User Agent Modal -->
        <Teleport to="body">
            <div v-if="showUaModal" class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-lg overflow-hidden shadow-2xl p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-sm text-slate-800">Raw User Agent Forensik</h4>
                        <button @click="showUaModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="bg-slate-900 text-emerald-400 font-mono text-[11px] p-4 rounded-xl break-all leading-relaxed max-h-48 overflow-y-auto">
                        {{ selectedUa }}
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button @click="showUaModal = false" class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
