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
        (log.notes?.toLowerCase().includes(q))
    );
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">Riwayat Konseling (Tindak Lanjut)</h2>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 md:p-8 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex-1">
                    <h3 class="text-2xl font-black mb-2">Riwayat Pemanggilan Siswa</h3>
                    <p class="text-blue-100 font-medium leading-relaxed">
                        Halaman ini menampilkan seluruh riwayat catatan konseling atau pemanggilan orang tua yang telah dilakukan oleh Guru BK. Siswa yang ada di daftar ini telah direset status peringatan keterlambatannya.
                    </p>
                </div>
                <div class="hidden md:flex bg-white/20 p-4 rounded-xl items-center justify-center shrink-0">
                    <div class="text-center">
                        <div class="text-4xl font-black">{{ logs.length }}</div>
                        <div class="text-sm font-bold text-blue-100 uppercase tracking-wider">Total Kasus</div>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div class="relative w-full md:w-96">
                    <input 
                        type="text" 
                        v-model="searchQuery" 
                        placeholder="Cari nama siswa, NISN, atau catatan..." 
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
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Ditangani Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">
                                    {{ new Date(log.created_at).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-800">{{ log.student?.name }}</div>
                                    <div class="text-sm font-medium text-blue-600">{{ log.student?.school_class?.name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 max-w-md whitespace-pre-wrap">
                                    {{ log.notes }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-medium">
                                    {{ log.user?.name }}
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
