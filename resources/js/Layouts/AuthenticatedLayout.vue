<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const page = usePage();
const user = page.props.auth.user;
const role = user.role;

const showingNavigationDropdown = ref(false);

const getMenus = () => {
    switch (role) {
        case 'admin':
            return [
                { name: 'Dashboard Admin', route: 'admin.dashboard', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' },
                { name: 'Data Pengguna', route: 'admin.users.index', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
                { name: 'Data Kelas', route: 'admin.classes.index', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
                { name: 'Data Siswa', route: 'admin.students.index', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
                { name: 'Input Keterlambatan', route: 'guru_piket.dashboard', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
                { name: 'Siswa Bimbingan', route: 'wali_kelas.dashboard', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
                { name: 'Ruang Pantau BK', route: 'guru_bk.dashboard', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
                { name: 'Riwayat Konseling', route: 'guru_bk.history', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
                { name: 'Analisa', route: 'kepala_sekolah.dashboard', icon: 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z' },
                { name: 'Riwayat Keterlambatan', route: 'laporan.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            ];
        case 'guru_piket':
            return [
                { name: 'Input Keterlambatan', route: 'guru_piket.dashboard', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
                { name: 'Riwayat Keterlambatan', route: 'laporan.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            ];
        case 'wali_kelas':
            return [
                { name: 'Siswa Bimbingan', route: 'wali_kelas.dashboard', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
                { name: 'Riwayat Keterlambatan', route: 'laporan.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            ];
        case 'kepala_sekolah':
            return [
                { name: 'Analisa', route: 'kepala_sekolah.dashboard', icon: 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z' },
                { name: 'Riwayat Keterlambatan', route: 'laporan.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            ];
        case 'guru_bk':
            return [
                { name: 'Ruang Pantau BK', route: 'guru_bk.dashboard', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
                { name: 'Riwayat Konseling', route: 'guru_bk.history', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
                { name: 'Riwayat Keterlambatan', route: 'laporan.index', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
            ];
        default:
            return [];
    }
};

const menus = getMenus();
</script>

<template>
    <div class="h-screen overflow-hidden bg-slate-50 flex font-sans">
        <!-- Sidebar -->
        <aside class="w-64 lg:w-72 bg-white border-r border-slate-200 text-slate-800 flex-shrink-0 hidden md:flex flex-col relative overflow-hidden">
            <!-- Sidebar Background Pattern Removed for Light Mode -->

            <div class="h-20 flex items-center px-6 bg-slate-50 relative z-10 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <ApplicationLogo class="w-10 h-10 drop-shadow-sm" />
                    <div>
                        <div class="text-lg font-bold tracking-tight text-slate-800 leading-tight">Presensi Pembinaan</div>
                        <div class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">SMKN 5 Telkom</div>
                    </div>
                </div>
            </div>

            <div class="p-6 relative z-10 flex-1 overflow-y-auto">
                <div class="text-xs uppercase tracking-wider text-slate-500 font-bold mb-4 ml-2">Menu Utama</div>
                <nav class="space-y-2">
                    <Link v-for="menu in menus" :key="menu.name" :href="route(menu.route)" :class="[
                        'flex items-center px-4 py-3 rounded-xl transition-all duration-300 group',
                        route().current(menu.route) ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-100 hover:text-blue-600 font-medium'
                    ]">
                        <svg :class="['w-5 h-5 mr-3 transition-transform duration-300', route().current(menu.route) ? 'transform scale-110' : 'opacity-75 group-hover:opacity-100 group-hover:scale-110']"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="menu.icon" />
                        </svg>
                        <span>{{ menu.name }}</span>
                    </Link>
                </nav>
            </div>

            <div class="mt-auto p-6 relative z-10">
                <div
                    class="bg-slate-50 rounded-2xl p-4 flex items-center space-x-3 border border-slate-200 shadow-sm">
                    <div
                        class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold border border-blue-200 shrink-0">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-sm font-bold text-slate-800 truncate">{{ user.name }}</div>
                        <div class="text-xs text-blue-600 font-semibold capitalize">{{ role.replace('_', ' ') }}</div>
                    </div>
                </div>
                <div class="mt-4 space-y-1">
                    <Link :href="route('profile.edit')"
                        class="w-full flex items-center space-x-2 text-sm text-slate-600 hover:text-blue-600 py-2 transition-colors group">
                        <svg class="w-4 h-4 group-hover:rotate-45 transition-transform" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Ganti Password</span>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button"
                        class="w-full flex items-center space-x-2 text-sm text-slate-600 hover:text-red-600 py-2 transition-colors group">
                        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Mobile Header -->
            <header class="md:hidden bg-white shadow-sm h-16 flex items-center justify-between px-4 z-20 relative">
                <div class="flex items-center space-x-2">
                    <ApplicationLogo class="w-8 h-8 drop-shadow-sm" />
                    <span class="text-lg font-bold text-slate-800">Presensi Pembinaan</span>
                </div>
                <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                    class="text-slate-500 hover:text-blue-600 transition-colors p-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round"
                            stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </header>

            <!-- Mobile Menu -->
            <div v-if="showingNavigationDropdown"
                class="md:hidden bg-white text-slate-800 shadow-xl absolute w-full z-50 top-16 border-t border-slate-100">
                <div class="px-4 py-4 space-y-2">
                    <Link v-for="menu in menus" :key="menu.name" :href="route(menu.route)" :class="[
                        'block px-4 py-3 rounded-xl text-base font-medium transition-colors',
                        route().current(menu.route) ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50'
                    ]" @click="showingNavigationDropdown = false">
                        {{ menu.name }}
                    </Link>
                    <div class="border-t border-slate-100 my-2 pt-2"></div>
                    <Link :href="route('profile.edit')"
                        class="block w-full text-left px-4 py-3 rounded-xl text-base font-medium text-slate-600 hover:bg-slate-50">
                        Ganti Password
                    </Link>
                    <Link :href="route('logout')" method="post" as="button"
                        class="block w-full text-left px-4 py-3 rounded-xl text-base font-medium text-red-600 hover:bg-slate-50">
                        Keluar
                    </Link>
                </div>
            </div>

            <!-- Page Header -->
            <header
                class="bg-white/80 backdrop-blur-md shadow-sm z-10 hidden md:block sticky top-0 border-b border-slate-200/50"
                v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-5 px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-slate-50 p-4 md:p-6 lg:p-8 flex flex-col min-h-0">
                <div class="animate-fade-in max-w-7xl mx-auto w-full flex-grow">
                    <slot />
                </div>
                <div class="w-full text-center mt-auto pt-8 pb-2">
                    <span class="text-[10px] text-slate-400/80 font-bold tracking-[0.2em] uppercase">PPKPMUINARRANIRY2026</span>
                </div>
            </main>
        </div>
    </div>
</template>

<style>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.4s ease-out forwards;
}
</style>
