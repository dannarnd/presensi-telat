<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    loginLogs: Object,
    filters: Object,
    stats: Object,
});

// Search & Filters
const search = ref(props.filters.search || '');
const status = ref(props.filters.status || 'all');
const role = ref(props.filters.role || 'all');
const date = ref(props.filters.date || '');

const handleFilter = () => {
    const url = (typeof route === 'function' && route().has('admin.login_logs.index'))
        ? route('admin.login_logs.index')
        : '/admin/login-logs';

    router.get(url, {
        search: search.value || undefined,
        status: status.value !== 'all' ? status.value : undefined,
        role: role.value !== 'all' ? role.value : undefined,
        date: date.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    status.value = 'all';
    role.value = 'all';
    date.value = '';
    handleFilter();
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
    const url = (typeof route === 'function' && route().has('admin.login_logs.destroy'))
        ? route('admin.login_logs.destroy', logToDelete.value.id)
        : `/admin/login-logs/${logToDelete.value.id}`;

    deleteForm.delete(url, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            logToDelete.value = null;
        }
    });
};

// Clear / Purge Logs Modal
const showClearModal = ref(false);
const clearScope = ref('all');
const clearForm = useForm({
    scope: 'all'
});

const openClearModal = () => {
    clearScope.value = 'all';
    showClearModal.value = true;
};

const submitClear = () => {
    const url = (typeof route === 'function' && route().has('admin.login_logs.clear'))
        ? route('admin.login_logs.clear')
        : '/admin/login-logs/clear';

    clearForm.scope = clearScope.value;
    clearForm.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            showClearModal.value = false;
        }
    });
};

// Raw User-Agent Forensic Modal
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
        second: '2-digit',
    });
};

const getRoleBadge = (userRole) => {
    switch (userRole) {
        case 'admin':
            return { label: 'Admin', class: 'bg-purple-50 text-purple-700 border-purple-200' };
        case 'guru_piket':
            return { label: 'Guru Piket', class: 'bg-blue-50 text-blue-700 border-blue-200' };
        case 'guru_bk':
            return { label: 'Guru BK', class: 'bg-teal-50 text-teal-700 border-teal-200' };
        case 'wali_kelas':
            return { label: 'Wali Kelas', class: 'bg-amber-50 text-amber-800 border-amber-200' };
        case 'kepala_sekolah':
            return { label: 'Kepala Sekolah', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
        default:
            return { label: userRole ? userRole.replace('_', ' ') : 'Tidak Terdaftar', class: 'bg-slate-100 text-slate-600 border-slate-200' };
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black bg-blue-100 text-blue-800 uppercase tracking-wider">
                            Keamanan & Audit
                        </span>
                        <h2 class="font-extrabold text-2xl text-slate-800 leading-tight">Log Aktivitas & Riwayat Login</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Memantau riwayat login seluruh pengguna, perangkat, alamat IP, serta mendeteksi percobaan akses ilegal atau password salah.
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <button
                        @click="openClearModal"
                        class="inline-flex items-center px-4 py-2 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 hover:border-rose-300 rounded-xl text-xs font-bold shadow-sm transition-all active:scale-95">
                        <svg class="w-4 h-4 mr-1.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Bersihkan Riwayat Log
                    </button>
                </div>
            </div>
        </template>

        <div class="py-4 md:py-6 max-w-7xl mx-auto space-y-6">

            <!-- Flash Success Message -->
            <div v-if="$page.props.flash.success" class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between animate-fade-in">
                <div class="flex items-center space-x-3">
                    <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-emerald-800 text-sm font-semibold">{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Log</div>
                        <div class="text-2xl font-black text-slate-800">{{ stats?.total ?? 0 }} <span class="text-xs font-normal text-slate-400">rekaman</span></div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Login Hari Ini</div>
                        <div class="text-2xl font-black text-emerald-600">{{ stats?.success_today ?? 0 }} <span class="text-xs font-normal text-slate-400">sukses</span></div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div :class="['w-12 h-12 rounded-xl flex items-center justify-center shrink-0', (stats?.failed_today ?? 0) > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400']">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Percobaan Gagal Hari Ini</div>
                        <div :class="['text-2xl font-black', (stats?.failed_today ?? 0) > 0 ? 'text-rose-600' : 'text-slate-800']">
                            {{ stats?.failed_today ?? 0 }} <span class="text-xs font-normal text-slate-400">kali</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Penyimpanan Supabase</div>
                        <div class="text-sm font-black text-slate-800">Ringan & Terindeks</div>
                        <div class="text-[11px] text-slate-500 font-medium">Bisa dibersihkan kapan saja</div>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex flex-col lg:flex-row items-center justify-between gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 w-full flex-1">
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            v-model="search"
                            @keyup.enter="handleFilter"
                            placeholder="Cari nama, username, IP, HP..."
                            class="pl-9 pr-4 py-2 w-full text-xs rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                        />
                    </div>

                    <div>
                        <select
                            v-model="status"
                            @change="handleFilter"
                            class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm text-slate-700">
                            <option value="all">Semua Status</option>
                            <option value="success">Berhasil Masuk</option>
                            <option value="failed">Gagal / Ditolak</option>
                        </select>
                    </div>

                    <div>
                        <select
                            v-model="role"
                            @change="handleFilter"
                            class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm text-slate-700">
                            <option value="all">Semua Peran</option>
                            <option value="admin">Admin</option>
                            <option value="guru_piket">Guru Piket</option>
                            <option value="guru_bk">Guru BK</option>
                            <option value="wali_kelas">Wali Kelas</option>
                            <option value="kepala_sekolah">Kepala Sekolah</option>
                        </select>
                    </div>

                    <div>
                        <input
                            type="date"
                            v-model="date"
                            @change="handleFilter"
                            class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm text-slate-700"
                        />
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end lg:self-center">
                    <button
                        @click="handleFilter"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all active:scale-95">
                        Terapkan
                    </button>
                    <button
                        v-if="search || status !== 'all' || role !== 'all' || date"
                        @click="resetFilter"
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition-all">
                        Reset
                    </button>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/75">
                            <tr>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Pengguna & Peran
                                </th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Status & Keterangan
                                </th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Waktu Presisi
                                </th>
                                <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Perangkat & Alamat IP
                                </th>
                                <th class="px-5 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-if="!loginLogs?.data || loginLogs.data.length === 0">
                                <td colspan="5" class="py-12 text-center text-slate-400 text-sm">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Belum ada catatan aktivitas login yang ditemukan.
                                </td>
                            </tr>

                            <tr v-for="log in loginLogs.data" :key="log.id" class="hover:bg-slate-50/60 transition-colors">
                                <!-- Pengguna & Peran -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div :class="['w-9 h-9 rounded-xl flex items-center justify-center font-black text-sm shrink-0', log.status === 'success' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700']">
                                            {{ (log.user_name || log.attempted_login || '?').charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-800 text-sm leading-snug">
                                                {{ log.user_name || log.attempted_login || 'Pengguna Tidak Dikenal' }}
                                            </div>
                                            <div class="flex items-center space-x-2 mt-0.5">
                                                <span :class="['px-2 py-0.5 rounded text-[10px] font-bold border', getRoleBadge(log.user_role).class]">
                                                    {{ getRoleBadge(log.user_role).label }}
                                                </span>
                                                <span v-if="log.user_email" class="text-xs text-slate-400">
                                                    {{ log.user_email }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Status & Keterangan -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div>
                                        <span v-if="log.status === 'success'" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Berhasil Masuk
                                        </span>
                                        <span v-else class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Gagal Login
                                        </span>
                                    </div>
                                    <div v-if="log.failure_reason" class="text-xs text-rose-600 font-semibold mt-1">
                                        {{ log.failure_reason }}
                                    </div>
                                    <div v-if="log.attempted_login && log.status === 'failed'" class="text-[11px] text-slate-400">
                                        Input: <span class="font-mono">{{ log.attempted_login }}</span>
                                    </div>
                                </td>

                                <!-- Waktu Presisi -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="text-xs font-extrabold text-slate-800">
                                        {{ formatDate(log.created_at) }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        WIB (Waktu Server)
                                    </div>
                                </td>

                                <!-- Perangkat & Alamat IP -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold text-slate-700">
                                            {{ log.device || 'Perangkat Tidak Dikenal' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            IP: {{ log.ip_address || 'Unknown' }}
                                        </span>
                                        <button
                                            v-if="log.user_agent"
                                            @click="openUaModal(log.user_agent)"
                                            class="text-[10px] text-blue-600 hover:text-blue-800 font-semibold underline">
                                            User-Agent
                                        </button>
                                    </div>
                                </td>

                                <!-- Aksi Hapus -->
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    <button
                                        @click="openDeleteModal(log)"
                                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                        title="Hapus baris log ini">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="loginLogs?.links && loginLogs.links.length > 3" class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
                    <div class="text-xs text-slate-500 font-medium">
                        Menampilkan <strong>{{ loginLogs.from || 0 }}</strong> - <strong>{{ loginLogs.to || 0 }}</strong> dari <strong>{{ loginLogs.total || 0 }}</strong> catatan
                    </div>
                    <div class="flex items-center space-x-1">
                        <Link
                            v-for="(link, idx) in loginLogs.links"
                            :key="idx"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-bold transition-colors',
                                link.active ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100',
                                !link.url ? 'opacity-40 cursor-not-allowed pointer-events-none' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Single Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center space-x-3 text-rose-600">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Hapus Catatan Log?</h3>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Apakah Anda yakin ingin menghapus catatan log aktivitas ini secara permanen? Data yang dihapus tidak dapat dipulihkan.
                </p>

                <div v-if="logToDelete" class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs text-slate-700 space-y-1">
                    <div><strong>Pengguna:</strong> {{ logToDelete.user_name || logToDelete.attempted_login }}</div>
                    <div><strong>Waktu:</strong> {{ formatDate(logToDelete.created_at) }}</div>
                    <div><strong>IP & Perangkat:</strong> {{ logToDelete.ip_address }} • {{ logToDelete.device }}</div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button
                        type="button"
                        @click="showDeleteModal = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitDelete"
                        :disabled="deleteForm.processing"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-md transition-colors disabled:opacity-50">
                        {{ deleteForm.processing ? 'Menghapus...' : 'Ya, Hapus' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Clear / Purge Logs Modal -->
        <div v-if="showClearModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
                <div class="flex items-center space-x-3 text-rose-600">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Bersihkan Riwayat Log</h3>
                        <p class="text-xs text-slate-500">Pilih rentang data log yang ingin dihapus dari database</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700">Pilih Cakupan Pembersihan:</label>
                    <div class="space-y-2">
                        <label class="flex items-start space-x-3 p-3 rounded-xl border cursor-pointer transition-colors" :class="clearScope === 'all' ? 'border-rose-300 bg-rose-50/50' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="all" v-model="clearScope" class="mt-0.5 text-rose-600 focus:ring-rose-500" />
                            <div>
                                <div class="text-xs font-bold text-slate-800">Bersihkan Semua Riwayat Log</div>
                                <div class="text-[11px] text-slate-500">Mengosongkan seluruh catatan aktivitas login dari awal sampai sekarang.</div>
                            </div>
                        </label>

                        <label class="flex items-start space-x-3 p-3 rounded-xl border cursor-pointer transition-colors" :class="clearScope === 'older_30_days' ? 'border-rose-300 bg-rose-50/50' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="older_30_days" v-model="clearScope" class="mt-0.5 text-rose-600 focus:ring-rose-500" />
                            <div>
                                <div class="text-xs font-bold text-slate-800">Hapus Log Lebih Dari 30 Hari Lalu</div>
                                <div class="text-[11px] text-slate-500">Hanya menghapus catatan lama, data 30 hari terakhir tetap tersimpan aman.</div>
                            </div>
                        </label>

                        <label class="flex items-start space-x-3 p-3 rounded-xl border cursor-pointer transition-colors" :class="clearScope === 'failed_only' ? 'border-rose-300 bg-rose-50/50' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" value="failed_only" v-model="clearScope" class="mt-0.5 text-rose-600 focus:ring-rose-500" />
                            <div>
                                <div class="text-xs font-bold text-slate-800">Hanya Hapus Percobaan Gagal Login</div>
                                <div class="text-[11px] text-slate-500">Membersihkan log gagal/salah password, membiarkan log login sukses.</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button
                        type="button"
                        @click="showClearModal = false"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitClear"
                        :disabled="clearForm.processing"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-md transition-colors disabled:opacity-50">
                        {{ clearForm.processing ? 'Memproses...' : 'Eksekusi Pembersihan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Raw User-Agent Forensic Modal -->
        <div v-if="showUaModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-extrabold text-slate-800">Raw User Agent Forensik</h3>
                    <button @click="showUaModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-slate-900 text-emerald-400 p-4 rounded-xl font-mono text-xs break-all leading-relaxed shadow-inner">
                    {{ selectedUa }}
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="button"
                        @click="showUaModal = false"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
