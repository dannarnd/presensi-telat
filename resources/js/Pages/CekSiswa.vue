<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';

const nis = ref('');
const isLoading = ref(false);
const errorMsg = ref('');
const studentData = ref(null);

const searchStudent = async () => {
    if (!nis.value) return;
    
    isLoading.value = true;
    errorMsg.value = '';
    studentData.value = null;
    
    try {
        const response = await axios.post('/api/cek-siswa', { nis: nis.value });
        studentData.value = response.data.student;
    } catch (error) {
        if (error.response && error.response.status === 404) {
            errorMsg.value = 'Data siswa dengan NIS tersebut tidak ditemukan.';
        } else {
            errorMsg.value = 'Terjadi kesalahan sistem, silakan coba lagi.';
        }
    } finally {
        isLoading.value = false;
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Cek Kehadiran Siswa" />

    <div class="min-h-screen bg-slate-50 flex flex-col font-sans">
        <!-- Background Elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-pulse"></div>
            <div class="absolute top-40 -left-40 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <!-- Header -->
        <header class="bg-purple-700 text-white p-4 sm:p-6 shadow-md relative z-10">
            <div class="max-w-3xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="Logo SMK" class="h-10 w-auto bg-white p-1 rounded-full shadow-sm">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight leading-none">PORTAL WALI MURID</h1>
                        <p class="text-purple-200 text-xs sm:text-sm font-medium mt-1">SMKN 5 Telkom Banda Aceh</p>
                    </div>
                </div>
                <Link href="/login" class="bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors">
                    Kembali
                </Link>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 w-full max-w-3xl mx-auto p-4 sm:p-6 relative z-10">
            
            <!-- Search Box -->
            <div class="bg-white rounded-3xl shadow-xl shadow-purple-900/5 p-6 sm:p-8 mb-6 border border-slate-100">
                <h2 class="text-lg font-bold text-slate-800 mb-2">Cek Catatan Keterlambatan Anak Anda</h2>
                <p class="text-sm text-slate-500 mb-6">Masukkan Nomor Induk Siswa (NIS) untuk melihat riwayat kehadiran.</p>
                
                <form @submit.prevent="searchStudent" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <input type="text" v-model="nis" required
                            class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                            placeholder="Contoh: 12345" />
                        <svg class="w-6 h-6 text-slate-400 absolute left-4 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                    </div>
                    <button type="submit" :disabled="isLoading || !nis"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-xl shadow-md shadow-purple-500/30 transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center">
                        <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-else>Cari Data</span>
                    </button>
                </form>
                
                <div v-if="errorMsg" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm font-medium rounded-lg flex items-start">
                    <svg class="w-5 h-5 mr-2 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{ errorMsg }}
                </div>
            </div>

            <!-- Results -->
            <div v-if="studentData" class="animate-fade-in space-y-6">
                <!-- Identity Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                            <span class="text-2xl font-black text-purple-600">{{ studentData.name.charAt(0) }}</span>
                        </div>
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-800">{{ studentData.name }}</h2>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider">
                                    NIS: {{ studentData.nis }}
                                </span>
                                <span class="bg-blue-100 text-blue-700 px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider">
                                    {{ studentData.school_class?.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="bg-slate-50 border-b border-slate-100 p-4 sm:p-6 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-800">Riwayat Keterlambatan</h3>
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-black">
                            Total: {{ studentData.delay_logs.length }} Kali
                        </span>
                    </div>
                    
                    <div class="p-4 sm:p-6">
                        <div v-if="studentData.delay_logs.length === 0" class="text-center py-8">
                            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h4 class="font-bold text-slate-800">Luar Biasa!</h4>
                            <p class="text-slate-500 text-sm mt-1">Anak Anda tidak memiliki catatan keterlambatan.</p>
                        </div>
                        
                        <div v-else class="relative border-l-2 border-slate-100 ml-3 md:ml-4 space-y-8">
                            <div v-for="(log, idx) in studentData.delay_logs" :key="log.id" class="relative pl-6">
                                <!-- Timeline Dot -->
                                <div class="absolute w-4 h-4 bg-red-500 rounded-full -left-[9px] top-1 border-4 border-white shadow-sm"></div>
                                
                                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2 mb-2">
                                        <div>
                                            <div class="text-sm font-bold text-slate-800">{{ formatDate(log.delay_time) }}</div>
                                            <div class="text-xs text-slate-500 font-medium mt-0.5">Pukul {{ formatTime(log.delay_time) }}</div>
                                        </div>
                                        <div class="bg-white border border-slate-200 text-slate-600 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm">
                                            Alasan: {{ log.reason }}
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-3 pt-3 border-t border-slate-200">
                                        Tercatat oleh petugas di gerbang.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="studentData.delay_logs.length >= 3" class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start">
                    <svg class="w-6 h-6 text-red-500 shrink-0 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <h4 class="font-bold text-red-800 text-sm">Perhatian Orang Tua</h4>
                        <p class="text-red-600 text-xs mt-1">Anak Anda telah terlambat 3 kali atau lebih. Mohon bimbingannya di rumah agar dapat hadir tepat waktu.</p>
                    </div>
                </div>
            </div>

            <!-- Footer note -->
            <p class="text-center text-xs text-slate-400 font-medium py-8 relative z-10">
                PPKPMUINARRANIRY2026 &copy; SMKN 5 Telkom Banda Aceh
            </p>
        </main>
    </div>
</template>
