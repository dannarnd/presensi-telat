<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    totalUsers: Number,
    totalClasses: Number,
    totalStudents: Number,
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">Dashboard Admin</h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Stat Card 1 -->
                <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Pengguna</div>
                        <div class="text-3xl font-black text-slate-800">{{ totalUsers }}</div>
                    </div>
                </div>

                <!-- Stat Card 2 -->
                <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Kelas</div>
                        <div class="text-3xl font-black text-slate-800">{{ totalClasses }}</div>
                    </div>
                </div>

                <!-- Stat Card 3 -->
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
            
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-2xl p-6 flex items-start space-x-4">
                <svg class="w-6 h-6 text-blue-500 mt-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <div>
                    <h4 class="font-bold text-blue-900 text-lg">Pusat Kendali Admin</h4>
                    <p class="text-blue-700 mt-1">Gunakan navigasi di sebelah kiri untuk mengelola Data Pengguna (Wali Kelas, dll), Data Kelas, dan Data Siswa secara terpisah. Anda juga memiliki akses penuh ke halaman Guru Piket dan Wali Kelas.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
