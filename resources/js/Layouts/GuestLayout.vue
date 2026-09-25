<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div
        class="min-h-screen flex items-center justify-center bg-slate-900 relative overflow-hidden px-4 py-8"
    >
        <!-- Background Elements -->
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10"></div>
        <div class="absolute -top-40 -right-40 w-72 h-72 sm:w-96 sm:h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-72 h-72 sm:w-96 sm:h-96 bg-slate-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000"></div>
        
        <div class="z-10 w-full max-w-md">
            <div class="text-center mb-6">
                <Link href="/" class="inline-block">
                    <!-- Telkom/SMK 5 Branding -->
                    <ApplicationLogo class="w-16 h-16 sm:w-24 sm:h-24 mx-auto mb-3 drop-shadow-2xl transform transition hover:scale-105 duration-300" />
                </Link>
                <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Presensi Telat</h1>
                <p class="text-slate-400 mt-1 text-xs sm:text-sm font-medium">SMKN 5 Telkom Banda Aceh</p>
            </div>

            <div
                class="overflow-hidden bg-white/10 backdrop-blur-xl border border-white/20 px-5 py-6 sm:px-8 sm:py-8 shadow-2xl rounded-2xl sm:rounded-3xl"
            >
                <slot />
            </div>
            
            <div class="mt-6 text-center text-slate-500 text-xs">
                &copy; {{ new Date().getFullYear() }} SMKN 5 Telkom Banda Aceh
            </div>
        </div>
    </div>
</template>

<style>
@keyframes blob {
  0% { transform: translate(0px, 0px) scale(1); }
  33% { transform: translate(30px, -50px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.9); }
  100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
  animation: blob 7s infinite;
}
.animation-delay-2000 {
  animation-delay: 2s;
}
</style>
