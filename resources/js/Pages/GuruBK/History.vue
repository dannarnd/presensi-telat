<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    logs: Array,
});

const searchQuery = ref('');

const filteredLogs = computed(() => {
    if (!searchQuery.value) return props.logs;
    const q = searchQuery.value.toLowerCase();
    return props.logs.filter(log => 
        (log.student?.name?.toLowerCase().includes(q)) || 
        (log.student?.nisn?.toLowerCase().includes(q)) ||
        (log.student?.school_class?.name?.toLowerCase().includes(q)) ||
        (log.counselor_name?.toLowerCase().includes(q)) ||
        (log.user?.name?.toLowerCase().includes(q)) ||
        (log.notes?.toLowerCase().includes(q))
    );
});

const printHistory = () => {
    window.print();
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center no-print">
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">Riwayat Konseling (Tindak Lanjut)</h2>
                <button 
                    @click="printHistory" 
                    class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-sm rounded-xl shadow-md transition-all active:scale-95"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak Laporan Fisik
                </button>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 md:p-8 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-6 no-print">
                <div class="flex-1">
                    <h3 class="text-2xl font-black mb-2">Riwayat Pemanggilan & Konseling Siswa</h3>
                    <p class="text-blue-100 font-medium leading-relaxed">
                        Halaman ini menampilkan seluruh riwayat catatan konseling resmi yang telah dilakukan oleh Guru BK. Setiap penanganan mencatat identitas Guru BK penindak secara formal dan akurat.
                    </p>
                </div>
                <div class="hidden md:flex bg-white/20 p-4 rounded-xl items-center justify-center shrink-0">
                    <div class="text-center">
                        <div class="text-4xl font-black">{{ logs.length }}</div>
                        <div class="text-sm font-bold text-blue-100 uppercase tracking-wider">Total Kasus</div>
                    </div>
                </div>
            </div>

            <!-- Print Header (Only visible when printing) -->
            <div class="hidden print:block pb-4 mb-4 border-b-2 border-slate-900 text-center">
                <h1 class="text-xl font-bold uppercase tracking-wider">SMK NEGERI 5 TELKOM BANDA ACEH</h1>
                <h2 class="text-base font-semibold text-slate-700">Laporan Resmi Tindak Lanjut Bimbingan Konseling (BK)</h2>
                <p class="text-xs text-slate-500 mt-1">Dicetak pada: {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
            </div>

            <!-- Search Bar -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between no-print">
                <div class="relative w-full md:w-96">
                    <input 
                        type="text" 
                        v-model="searchQuery" 
                        placeholder="Cari siswa, NISN, kelas, guru BK, atau catatan..." 
                        class="w-full rounded-xl border-slate-300 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm py-2.5 pl-10 transition-colors"
                    >
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-hidden shadow-lg shadow-slate-200/50 rounded-2xl border border-slate-100 flex flex-col min-h-[400px]">
                <div class="overflow-auto flex-1 p-0">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Siswa & Kelas</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Catatan Konseling</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Guru BK Penindak</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">
                                    {{ new Date(log.created_at).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ log.student?.name }}</div>
                                    <div class="text-xs font-semibold text-slate-500">{{ log.student?.school_class?.name }}</div>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            {{ log.stage || 'Bimbingan Konseling' }}
                                        </span>
                                        <span v-if="log.delay_count" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Telat ke-{{ log.delay_count }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 max-w-md whitespace-pre-wrap">
                                    {{ log.notes }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                        <span>{{ log.counselor_name || log.user?.name || 'Guru BK' }}</span>
                                    </div>
                                    <div v-if="log.counselor_name && log.user?.name && log.counselor_name !== log.user?.name" class="text-xs text-slate-400 mt-0.5 ml-3.5">
                                        Akun: {{ log.user.name }}
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredLogs.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    <div class="inline-flex justify-center items-center w-12 h-12 rounded-full bg-slate-100 mb-3">
                                        <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <p>{{ searchQuery ? 'Tidak ada riwayat konseling yang cocok dengan pencarian Anda.' : 'Belum ada riwayat konseling.' }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
