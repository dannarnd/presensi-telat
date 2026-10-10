<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    todayLogs: Array,
});

// Auto-refresh mechanism
let refreshInterval;

onMounted(() => {
    // Auto refresh every 60 seconds
    refreshInterval = setInterval(() => {
        router.reload({ only: ['todayLogs'], preserveState: true, preserveScroll: true });
    }, 60000);
});

onUnmounted(() => {
    if (refreshInterval) clearInterval(refreshInterval);
});

// Waktu saat ini
const currentTime = ref(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}, 1000);

const searchQuery = ref('');

// Group by Class
const groupedLogs = computed(() => {
    const groups = {};
    props.todayLogs.forEach(log => {
        const className = log.student?.school_class?.name || 'Lainnya';
        // Filter search
        const studentName = log.student?.name?.toLowerCase() || '';
        const q = searchQuery.value.toLowerCase();
        
        if (q && !studentName.includes(q) && !className.toLowerCase().includes(q)) {
            return; // Skip if not match search
        }

        if (!groups[className]) {
            groups[className] = {
                class_name: className,
                logs: []
            };
        }
        groups[className].logs.push(log);
    });
    return Object.values(groups).sort((a, b) => a.class_name.localeCompare(b.class_name));
});

</script>

<template>
    <Head title="Monitor Keterlambatan" />

    <div class="min-h-screen bg-slate-50 flex flex-col font-sans">
        <!-- Header -->
        <header class="bg-blue-600 text-white p-4 sm:p-6 shadow-md sticky top-0 z-10">
            <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <img src="/images/logo.png" alt="Logo SMK" class="h-10 w-auto bg-white p-1 rounded-full shadow-sm">
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight leading-none">MONITOR SISWA TERLAMBAT</h1>
                        <p class="text-blue-100 text-xs sm:text-sm font-medium mt-1">SMKN 5 Telkom Banda Aceh - Realtime</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="bg-blue-700/50 px-4 py-2 rounded-xl backdrop-blur-sm border border-blue-500/50 flex flex-col items-center">
                        <div class="text-[10px] text-blue-200 uppercase font-bold tracking-wider">Jam Saat Ini</div>
                        <div class="text-xl font-black font-mono">{{ currentTime }}</div>
                    </div>
                    <a href="/login" class="bg-white text-blue-600 hover:bg-blue-50 font-bold px-4 py-2 rounded-lg text-sm shadow-sm transition-colors">
                        Login Admin
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 w-full max-w-5xl mx-auto p-4 sm:p-6 space-y-6">
            
            <!-- Controls -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="flex items-center space-x-2 text-slate-600">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-sm font-bold">Auto-Update Aktif</span>
                </div>
                
                <div class="w-full sm:w-72 relative">
                    <input type="text" v-model="searchQuery" placeholder="Cari nama atau kelas..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50 border-slate-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="groupedLogs.length === 0" class="bg-white rounded-3xl p-12 text-center shadow-sm border border-slate-100">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-emerald-50 mb-4">
                    <svg class="w-10 h-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Belum ada siswa terlambat hari ini</h3>
                <p class="text-slate-500">Semua siswa masuk tepat waktu, atau data belum diinput oleh Guru Piket.</p>
            </div>

            <!-- List by Class -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="group in groupedLogs" :key="group.class_name" class="bg-white rounded-3xl overflow-hidden shadow-sm border border-slate-200 hover:shadow-md transition-shadow flex flex-col h-full">
                    
                    <div class="bg-slate-50 border-b border-slate-100 p-4 flex justify-between items-center sticky top-0">
                        <h2 class="text-xl font-black text-slate-800">{{ group.class_name }}</h2>
                        <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-lg shrink-0">
                            {{ group.logs.length }} Siswa
                        </span>
                    </div>

                    <div class="p-4 flex-1 space-y-3 overflow-y-auto max-h-80">
                        <div v-for="log in group.logs" :key="log.id" class="flex justify-between items-start p-3 bg-slate-50/50 rounded-xl border border-slate-100">
                            <div>
                                <h3 class="font-bold text-slate-800 text-sm leading-tight">{{ log.student?.name }}</h3>
                                <p class="text-[11px] text-slate-500 mt-1 font-medium bg-white px-2 py-0.5 rounded border border-slate-100 inline-block shadow-sm">
                                    {{ log.reason }}
                                </p>
                            </div>
                            <div class="text-xs font-black font-mono text-blue-600 bg-blue-50 px-2 py-1 rounded-md shrink-0 shadow-sm border border-blue-100">
                                {{ new Date(log.delay_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer note -->
            <p class="text-center text-xs text-slate-400 font-medium py-6">
                PPKPMUINARRANIRY2026 &copy; SMKN 5 Telkom Banda Aceh
            </p>
        </main>
    </div>
</template>
