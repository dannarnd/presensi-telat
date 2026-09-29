<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    totalUsers: Number,
    totalClasses: Number,
    totalStudents: Number,
    todayLogs: Array,
});

// Search Log Hari Ini
const logSearchQuery = ref('');
const filteredTodayLogs = computed(() => {
    if (!props.todayLogs) return [];
    if (!logSearchQuery.value) return props.todayLogs;
    const q = logSearchQuery.value.toLowerCase();
    return props.todayLogs.filter(log => 
        log.student?.name.toLowerCase().includes(q) || 
        log.student?.nisn?.toLowerCase().includes(q) ||
        log.student?.school_class?.name.toLowerCase().includes(q)
    );
});

const getWarningBadge = (count) => {
    if (count >= 5) {
        return { class: 'bg-red-100 text-red-800 border-red-200', text: 'Panggilan Ortu (≥5)' };
    } else if (count >= 3) {
        return { class: 'bg-orange-100 text-orange-800 border-orange-200', text: 'Peringatan (≥3)' };
    }
    return { class: 'bg-green-100 text-green-800 border-green-200', text: 'Aman' };
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">Dashboard Admin</h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Pengguna</div>
                        <div class="text-3xl font-black text-slate-800">{{ totalUsers }}</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Kelas</div>
                        <div class="text-3xl font-black text-slate-800">{{ totalClasses }}</div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Siswa</div>
                        <div class="text-3xl font-black text-slate-800">{{ totalStudents }}</div>
                    </div>
                </div>
            </div>

            <!-- ===== BACKUP PANEL ===== -->
            <div class="mt-8 bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 shadow-xl">
                <div class="flex items-center space-x-3 mb-5">
                    <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-white">Manajemen Backup Data</h3>
                        <p class="text-sm text-slate-400">Unduh salinan data sistem untuk keamanan dan arsip</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Backup Excel Harian -->
                    <a :href="route('admin.backup_daily')" class="group bg-white/5 hover:bg-emerald-600 border border-white/10 hover:border-emerald-500 rounded-xl p-5 flex items-start space-x-4 transition-all duration-200 cursor-pointer no-underline">
                        <div class="h-12 w-12 rounded-xl bg-emerald-500/20 group-hover:bg-white/20 flex items-center justify-center shrink-0 transition-colors">
                            <svg class="w-6 h-6 text-emerald-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-white">Backup Presensi Hari Ini</div>
                            <div class="text-sm text-slate-400 group-hover:text-emerald-100 mt-1">
                                Download rekaman keterlambatan hari ini dalam format 
                                <span class="font-bold text-emerald-400 group-hover:text-white">.xlsx (Excel)</span>
                            </div>
                        </div>
                    </a>


                </div>
                <p class="text-xs text-slate-500 mt-4">📌 Nama file otomatis menyertakan hari, tanggal, dan jam saat backup dilakukan.</p>
            </div>

            <!-- Log Hari Ini Panel -->
            <div class="mt-8 bg-white overflow-hidden shadow-lg shadow-slate-200/50 border border-slate-200 rounded-2xl flex flex-col">
                <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between md:items-center bg-slate-50 gap-4">
                    <div>
                        <h3 class="font-bold text-xl text-slate-800">Log Terlambat Hari Ini</h3>
                        <p class="text-sm text-slate-500 mt-1">Daftar siswa terlambat real-time</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative w-full sm:w-auto">
                            <input 
                                type="text" 
                                v-model="logSearchQuery" 
                                placeholder="Cari siswa/kelas..." 
                                class="w-full sm:w-64 text-sm border-slate-200 bg-white focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm py-2 pl-9 pr-3 transition-colors"
                            >
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div class="bg-blue-100 text-blue-700 font-bold px-3 py-1.5 rounded-lg text-sm whitespace-nowrap shadow-sm">
                            Jumlah: {{ filteredTodayLogs.length }} Siswa
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto flex-1 p-0">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-white">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Siswa &amp; Kelas</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Waktu &amp; Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr v-for="log in filteredTodayLogs" :key="log.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ log.student?.name }}</div>
                                    <div class="text-sm font-medium text-slate-500">{{ log.student?.school_class?.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900 mb-1">{{ new Date(log.delay_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</div>
                                    <span :class="['px-2 py-0.5 inline-flex text-[10px] leading-5 font-bold rounded border', getWarningBadge(log.student?.active_delay_logs_count).class]">
                                        {{ getWarningBadge(log.student?.active_delay_logs_count).text }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-800 font-medium">{{ log.reason }}</div>
                                    <div class="text-xs text-slate-500 mt-1 font-medium italic" v-if="log.reporter_name">Petugas: {{ log.reporter_name }}</div>
                                </td>
                            </tr>
                            <tr v-if="filteredTodayLogs.length === 0">
                                <td colspan="3" class="px-6 py-12 text-center">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <p class="text-slate-500 font-medium">
                                        {{ logSearchQuery ? 'Pencarian tidak ditemukan.' : 'Belum ada siswa terlambat hari ini.' }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Info Panel -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-2xl p-6 flex items-start space-x-4">
                <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <h4 class="font-bold text-blue-900 text-lg">Pusat Kendali Admin</h4>
                    <p class="text-blue-700 mt-1">Gunakan navigasi di sebelah kiri untuk mengelola Data Pengguna (Wali Kelas, dll), Data Kelas, dan Data Siswa secara terpisah. Anda juga memiliki akses penuh ke halaman Guru Piket dan Wali Kelas.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
