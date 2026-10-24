<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    counselingLogs: Object,
    filters: Object,
    stats: Object,
    classes: Array,
    counselors: Array,
});

// Search & Filters
const search = ref(props.filters.search || '');
const stage = ref(props.filters.stage || 'all');
const counselor = ref(props.filters.counselor || 'all');
const classId = ref(props.filters.class_id || 'all');
const date = ref(props.filters.date || '');

const handleFilter = () => {
    const url = (typeof route === 'function' && route().has('admin.counseling_logs.index'))
        ? route('admin.counseling_logs.index')
        : '/admin/counseling-logs';

    router.get(url, {
        search: search.value || undefined,
        stage: stage.value !== 'all' ? stage.value : undefined,
        counselor: counselor.value !== 'all' ? counselor.value : undefined,
        class_id: classId.value !== 'all' ? classId.value : undefined,
        date: date.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    stage.value = 'all';
    counselor.value = 'all';
    classId.value = 'all';
    date.value = '';
    handleFilter();
};

// Detail Modal
const showDetailModal = ref(false);
const selectedLog = ref(null);

const openDetailModal = (log) => {
    selectedLog.value = log;
    showDetailModal.value = true;
};

// Delete Single Log Modal
const showDeleteModal = ref(false);
const logToDelete = ref(null);
const deleteForm = useForm({});

const openDeleteModal = (log) => {
    logToDelete.value = log;
    showDeleteModal.value = true;
};

const submitDelete = () => {
    if (!logToDelete.value) return;
    const url = (typeof route === 'function' && route().has('admin.counseling_logs.destroy'))
        ? route('admin.counseling_logs.destroy', logToDelete.value.id)
        : `/admin/counseling-logs/${logToDelete.value.id}`;

    deleteForm.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            logToDelete.value = null;
        }
    });
};

// Clear Logs Modal
const showClearModal = ref(false);
const clearScope = ref('older_30_days');
const clearForm = useForm({
    scope: 'older_30_days',
});

const submitClear = () => {
    clearForm.scope = clearScope.value;
    const url = (typeof route === 'function' && route().has('admin.counseling_logs.clear'))
        ? route('admin.counseling_logs.clear')
        : '/admin/counseling-logs/clear';

    clearForm.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            showClearModal.value = false;
        }
    });
};

const printPage = () => {
    window.print();
};

const getStageBadge = (stageName) => {
    const s = stageName || '';
    if (s.includes('Tahap 3') || s.includes('SP') || s.includes('Konferensi')) {
        return {
            class: 'bg-purple-100 text-purple-800 border-purple-200',
            dot: 'bg-purple-500',
            label: 'Tahap 3 (SP / Konferensi)'
        };
    }
    if (s.includes('Tahap 2') || s.includes('Panggilan')) {
        return {
            class: 'bg-rose-100 text-rose-800 border-rose-200',
            dot: 'bg-rose-500',
            label: 'Tahap 2 (Panggilan Ortu)'
        };
    }
    return {
        class: 'bg-blue-100 text-blue-800 border-blue-200',
        dot: 'bg-blue-500',
        label: 'Tahap 1 (Bimbingan Siswa)'
    };
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
                <div>
                    <h2 class="font-black text-2xl text-slate-800 leading-tight flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-indigo-100 text-indigo-700">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </span>
                        Log Tindakan & Bimbingan BK
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">Audit terpusat rekam penanganan kedisiplinan dan pembinaan siswa oleh Guru BK</p>
                </div>

                <div class="flex items-center space-x-3">
                    <button 
                        @click="printPage" 
                        class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-xl shadow-md transition-all active:scale-95"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Laporan
                    </button>

                    <button 
                        @click="showClearModal = true" 
                        class="inline-flex items-center px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-sm rounded-xl border border-rose-200 transition-all active:scale-95"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Bersihkan Log
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stat Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 no-print">
                <!-- Total -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Kasus</span>
                    <div class="text-2xl font-black text-slate-800 mt-2">{{ stats.total ?? 0 }}</div>
                    <span class="text-[10px] text-slate-400 mt-1">Keseluruhan rekam</span>
                </div>

                <!-- Hari Ini -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Hari Ini</span>
                    <div class="text-2xl font-black text-indigo-700 mt-2 flex items-baseline gap-2">
                        {{ stats.today ?? 0 }}
                        <span v-if="stats.today > 0" class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800">Baru</span>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1">Tindakan hari ini</span>
                </div>

                <!-- Bulan Ini -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Bulan Ini</span>
                    <div class="text-2xl font-black text-slate-800 mt-2">{{ stats.this_month ?? 0 }}</div>
                    <span class="text-[10px] text-slate-400 mt-1">Periode aktif</span>
                </div>

                <!-- Tahap 1 -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Tahap 1 (Siswa)</span>
                    <div class="text-2xl font-black text-blue-700 mt-2">{{ stats.tahap1 ?? 0 }}</div>
                    <span class="text-[10px] text-slate-400 mt-1">Bimbingan awal</span>
                </div>

                <!-- Tahap 2 -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Tahap 2 (Ortu)</span>
                    <div class="text-2xl font-black text-rose-700 mt-2">{{ stats.tahap2 ?? 0 }}</div>
                    <span class="text-[10px] text-slate-400 mt-1">Panggilan orang tua</span>
                </div>

                <!-- Tahap 3 -->
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col justify-between">
                    <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Tahap 3 (SP)</span>
                    <div class="text-2xl font-black text-purple-700 mt-2">{{ stats.tahap3 ?? 0 }}</div>
                    <span class="text-[10px] text-slate-400 mt-1">Konferensi & SP</span>
                </div>
            </div>

            <!-- Print Header -->
            <div class="hidden print:block pb-4 mb-4 border-b-2 border-slate-900 text-center">
                <h1 class="text-xl font-bold uppercase tracking-wider">SMK NEGERI 5 TELKOM BANDA ACEH</h1>
                <h2 class="text-base font-semibold text-slate-700">Laporan Audit Log Tindakan Bimbingan Konseling (BK)</h2>
                <p class="text-xs text-slate-500 mt-1">Dicetak oleh Administrator pada: {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 space-y-4 no-print">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- Search -->
                    <div class="relative lg:col-span-2">
                        <input 
                            type="text" 
                            v-model="search" 
                            @keyup.enter="handleFilter"
                            placeholder="Cari nama siswa, NISN, Guru BK, catatan..." 
                            class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm py-2 pl-9 pr-3 transition-colors"
                        >
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Filter Tahap -->
                    <div>
                        <select 
                            v-model="stage" 
                            @change="handleFilter"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm py-2 transition-colors"
                        >
                            <option value="all">-- Semua Tahapan BK --</option>
                            <option value="tahap_1">Tahap 1 (Bimbingan Siswa)</option>
                            <option value="tahap_2">Tahap 2 (Panggilan Orang Tua)</option>
                            <option value="tahap_3">Tahap 3 (Konferensi Kasus / SP)</option>
                        </select>
                    </div>

                    <!-- Filter Kelas -->
                    <div>
                        <select 
                            v-model="classId" 
                            @change="handleFilter"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm py-2 transition-colors"
                        >
                            <option value="all">-- Semua Kelas --</option>
                            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <!-- Filter Guru BK / Konselor -->
                    <div>
                        <select 
                            v-model="counselor" 
                            @change="handleFilter"
                            class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm py-2 transition-colors"
                        >
                            <option value="all">-- Semua Guru BK --</option>
                            <option v-for="cn in counselors" :key="cn" :value="cn">{{ cn }}</option>
                        </select>
                    </div>
                </div>

                <!-- Tanggal & Tombol Aksi Filter -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-500">Pilih Tanggal:</label>
                        <input 
                            type="date" 
                            v-model="date" 
                            @change="handleFilter"
                            class="rounded-xl border-slate-200 bg-slate-50 text-xs py-1.5 focus:border-indigo-500 focus:ring-indigo-500"
                        >
                    </div>

                    <div class="flex items-center space-x-2">
                        <button 
                            @click="handleFilter" 
                            class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm"
                        >
                            Terapkan Filter
                        </button>
                        <button 
                            @click="resetFilter" 
                            class="px-4 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition-all"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white overflow-hidden shadow-lg shadow-slate-200/50 rounded-2xl border border-slate-100 flex flex-col min-h-[450px]">
                <div class="overflow-x-auto flex-1 p-0">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Waktu Tindakan</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Siswa & Kelas</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tahap & Titik Telat</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Guru BK Penindak</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Catatan Konseling</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider no-print">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr v-for="log in counselingLogs.data" :key="log.id" class="hover:bg-slate-50/80 transition-colors">
                                <!-- Waktu -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-bold text-slate-800">
                                        {{ new Date(log.created_at).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' }) }}
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                        {{ new Date(log.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WIB
                                    </div>
                                </td>

                                <!-- Siswa -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900">{{ log.student?.name || 'Siswa Dihapus' }}</div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs font-mono text-slate-500">{{ log.student?.nisn || '-' }}</span>
                                        <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-1.5 py-0.5 rounded">
                                            {{ log.student?.school_class?.name || '-' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Tahap & Delay Count -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border', getStageBadge(log.stage).class]">
                                        <span :class="['w-1.5 h-1.5 rounded-full', getStageBadge(log.stage).dot]"></span>
                                        {{ log.stage || 'Bimbingan Konseling' }}
                                    </span>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        <span v-if="log.delay_count" class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                            Ditindak saat Telat ke-{{ log.delay_count }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Guru BK -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ log.counselor_name || 'Guru BK' }}
                                    </div>
                                    <div v-if="log.user" class="text-[10px] text-slate-400 mt-0.5">
                                        Akun: @{{ log.user.username || log.user.name }}
                                    </div>
                                </td>

                                <!-- Catatan -->
                                <td class="px-6 py-4 max-w-xs">
                                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed bg-slate-50 p-2 rounded-lg border border-slate-100">
                                        "{{ log.notes }}"
                                    </p>
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4 whitespace-nowrap text-center space-x-1.5 no-print">
                                    <button 
                                        @click="openDetailModal(log)" 
                                        class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition-colors"
                                        title="Lihat Rincian Catatan"
                                    >
                                        Rincian
                                    </button>

                                    <button 
                                        @click="openDeleteModal(log)" 
                                        class="px-2.5 py-1.5 text-xs font-bold rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors"
                                        title="Hapus Catatan Log"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!counselingLogs.data || counselingLogs.data.length === 0">
                                <td colspan="6" class="text-center py-12 text-slate-400 text-sm">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    Tidak ada catatan log bimbingan BK yang cocok dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="counselingLogs.links && counselingLogs.links.length > 3" class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between no-print">
                    <div class="text-xs text-slate-500">
                        Menampilkan <span class="font-bold text-slate-700">{{ counselingLogs.from || 0 }}</span> - <span class="font-bold text-slate-700">{{ counselingLogs.to || 0 }}</span> dari <span class="font-bold text-slate-700">{{ counselingLogs.total || 0 }}</span> log
                    </div>
                    <div class="flex items-center space-x-1">
                        <Link 
                            v-for="(link, i) in counselingLogs.links" 
                            :key="i" 
                            :href="link.url || '#'" 
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 text-xs rounded-lg transition-colors',
                                link.active ? 'bg-indigo-600 text-white font-bold' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100',
                                !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <Teleport to="body">
            <div v-if="showDetailModal && selectedLog" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 animate-fade-in">
                    <!-- Modal Header -->
                    <div class="p-6 bg-gradient-to-r from-indigo-600 to-blue-600 text-white flex justify-between items-start">
                        <div>
                            <span class="text-xs uppercase tracking-wider text-indigo-200 font-bold">Rincian Dokumen Konseling BK</span>
                            <h3 class="text-xl font-black mt-1">{{ selectedLog.student?.name || 'Siswa' }}</h3>
                            <p class="text-xs text-indigo-100 mt-0.5">
                                NISN: {{ selectedLog.student?.nisn || '-' }} &bull; Kelas: {{ selectedLog.student?.school_class?.name || '-' }}
                            </p>
                        </div>
                        <button @click="showDetailModal = false" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 font-medium">Tahap Penanganan:</span>
                                <div class="font-bold text-slate-800 mt-0.5 flex items-center gap-1.5">
                                    <span :class="['w-2 h-2 rounded-full', getStageBadge(selectedLog.stage).dot]"></span>
                                    {{ selectedLog.stage || 'Bimbingan Konseling' }}
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-400 font-medium">Titik Keterlambatan:</span>
                                <div class="font-bold text-indigo-700 mt-0.5">
                                    Saat Telat ke-{{ selectedLog.delay_count || '?' }}
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-400 font-medium">Guru BK Penindak:</span>
                                <div class="font-bold text-slate-800 mt-0.5">
                                    {{ selectedLog.counselor_name || 'Guru BK' }}
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-400 font-medium">Waktu Pencatatan:</span>
                                <div class="font-bold text-slate-800 mt-0.5">
                                    {{ new Date(selectedLog.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }},
                                    {{ new Date(selectedLog.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WIB
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Konseling -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Isi Catatan Bimbingan & Kesepakatan:
                            </label>
                            <div class="p-4 bg-indigo-50/60 rounded-2xl border border-indigo-100 text-xs text-slate-800 leading-relaxed whitespace-pre-line font-medium">
                                {{ selectedLog.notes }}
                            </div>
                        </div>

                        <!-- Info Kontak Ortu -->
                        <div v-if="selectedLog.student?.no_wa_ortu" class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between">
                            <div class="text-xs">
                                <span class="text-emerald-700 font-bold">WhatsApp Orang Tua:</span>
                                <div class="font-mono text-slate-700 mt-0.5">{{ selectedLog.student.no_wa_ortu }}</div>
                            </div>
                            <a :href="`https://wa.me/${selectedLog.student.no_wa_ortu.replace(/^0/, '62')}`" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 transition-colors">
                                Hubungi
                            </a>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                        <button @click="showDetailModal = false" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Delete Single Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showDeleteModal && logToDelete" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-center border border-slate-100 animate-fade-in">
                    <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>

                    <h3 class="text-lg font-bold text-slate-800">Hapus Catatan Bimbingan BK?</h3>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Anda akan menghapus rekam bimbingan untuk siswa <strong>{{ logToDelete.student?.name }}</strong> ({{ logToDelete.stage }}).
                        Tindakan ini tidak dapat dibatalkan.
                    </p>

                    <div class="mt-6 flex items-center justify-center space-x-3">
                        <button 
                            @click="showDeleteModal = false" 
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            @click="submitDelete" 
                            :disabled="deleteForm.processing"
                            class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-colors disabled:opacity-50"
                        >
                            {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus Log' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Clear Logs Scope Modal -->
        <Teleport to="body">
            <div v-if="showClearModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-slate-100 animate-fade-in">
                    <div class="flex items-center space-x-3 text-rose-600 mb-4">
                        <div class="p-3 bg-rose-100 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Bersihkan Riwayat Log BK</h3>
                            <p class="text-xs text-slate-500">Pilih jangkauan penghapusan log bimbingan</p>
                        </div>
                    </div>

                    <div class="space-y-3 my-4">
                        <label class="flex items-start p-3 bg-slate-50 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-400 transition-colors">
                            <input type="radio" value="older_30_days" v-model="clearScope" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3 text-xs">
                                <div class="font-bold text-slate-800">Hanya yang lebih lama dari 30 hari</div>
                                <div class="text-slate-500 mt-0.5">Aman: mempertahankan log bimbingan aktif bulan ini.</div>
                            </div>
                        </label>

                        <label class="flex items-start p-3 bg-rose-50/50 rounded-xl border border-rose-200 cursor-pointer hover:border-rose-400 transition-colors">
                            <input type="radio" value="all" v-model="clearScope" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                            <div class="ml-3 text-xs">
                                <div class="font-bold text-rose-900">Bersihkan SEMUA log bimbingan</div>
                                <div class="text-rose-600 mt-0.5">Perhatian: Semua riwayat catatan konseling akan dikosongkan.</div>
                            </div>
                        </label>
                    </div>

                    <div class="mt-6 flex items-center justify-end space-x-3">
                        <button 
                            @click="showClearModal = false" 
                            class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            @click="submitClear" 
                            :disabled="clearForm.processing"
                            class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-colors disabled:opacity-50"
                        >
                            {{ clearForm.processing ? 'Memproses...' : 'Konfirmasi Bersihkan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
.animate-fade-in {
    animation: fade-in 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
