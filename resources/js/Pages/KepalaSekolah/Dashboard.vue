<script setup>
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Bar, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';
import axios from 'axios';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const props = defineProps({
    period: String,
    periodLabel: String,
    totalLates: Number,
    momPercentage: Number,
    momTrend: String,
    activeLogs: Number,
    resolvedLogs: Number,
    resolutionRate: Number,
    doughnutLabels: Array,
    doughnutData: Array,
    chartLabels: Array,
    chartData: Array,
    wallOfShame: Array,
    mostProblematicClass: Object,
});

const selectedPeriod = ref(props.period);

watch(selectedPeriod, (newPeriod) => {
    router.get(route('kepala_sekolah.dashboard'), { period: newPeriod }, {
        preserveState: true,
        replace: true
    });
});

const printUrl = ref('');
const printPdf = () => {
    printUrl.value = route('kepala_sekolah.pdf', { period: selectedPeriod.value, t: Date.now() });
};

// Helper status penyelesaian bimbingan BK
const isTahap3Done = (student) => {
    const late = student?.total_late || 0;
    const count = student?.counseling_count || 0;
    const lastDelay = student?.last_counseling_delay_count || 0;
    const lastStage = student?.last_counseling_stage || '';

    if (count === 0) return false;
    if (lastDelay >= late) return true;
    if (lastStage.includes('Tahap 3') || lastStage.includes('Konferensi') || lastStage.includes('SP')) return true;
    if (count >= 3) return true;
    return false;
};

const isTahap2Done = (student) => {
    const late = student?.total_late || 0;
    const count = student?.counseling_count || 0;
    const lastDelay = student?.last_counseling_delay_count || 0;
    const lastStage = student?.last_counseling_stage || '';

    if (count === 0) return false;
    if (lastDelay >= late) return true;
    if (lastStage.includes('Tahap 2') || lastStage.includes('Panggilan') || isTahap3Done(student)) return true;
    if (count >= 2) return true;
    return false;
};

const isTahap1Done = (student) => {
    const late = student?.total_late || 0;
    const count = student?.counseling_count || 0;
    const lastDelay = student?.last_counseling_delay_count || 0;

    if (count === 0) return false;
    if (lastDelay >= late) return true;
    if (count >= 1) return true;
    return false;
};

// Filter Tab 10 Besar (Perlu Tindakan BK vs Sudah Ditindaklanjuti)
const isStudentNeedsBK = (student) => {
    const late = student.total_late || 0;
    const lastDelay = student.last_counseling_delay_count || 0;
    if (late < 3) return false;

    // Jika bimbingan terakhir sudah mencakup titik keterlambatan saat ini (misal telat 6x dan sudah dibimbing di telat ke-6)
    if (lastDelay >= late) return false;

    if (late >= 6) {
        if (!isTahap3Done(student)) return true;
        if (lastDelay > 0 && late > lastDelay) return true; // Telat pasca-SP
        return false;
    }
    if (late >= 5) {
        if (!isTahap2Done(student)) return true;
        if (lastDelay > 0 && late > lastDelay) return true; // Telat pasca-panggilan
        return false;
    }
    if (late >= 3) {
        return !isTahap1Done(student);
    }
    return false;
};

const topTab = ref('active'); // 'active' | 'resolved' | 'all'

const activeTopStudents = computed(() => {
    return (props.wallOfShame || []).filter(s => isStudentNeedsBK(s));
});

const resolvedTopStudents = computed(() => {
    return (props.wallOfShame || []).filter(s => !isStudentNeedsBK(s) && (s.counseling_count || 0) > 0);
});

const filteredWallOfShame = computed(() => {
    if (topTab.value === 'active') {
        return activeTopStudents.value;
    } else if (topTab.value === 'resolved') {
        return resolvedTopStudents.value;
    }
    return props.wallOfShame || [];
});

// Bar Chart
const barChartConfig = {
    labels: props.chartLabels,
    datasets: [
        {
            label: 'Jumlah Keterlambatan',
            backgroundColor: '#4F46E5',
            data: props.chartData,
            borderRadius: 4,
        }
    ]
};

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            ticks: { stepSize: 1 }
        }
    }
};

// Doughnut Chart
const getColors = (count) => {
    const colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#14B8A6'];
    return Array(count).fill().map((_, i) => colors[i % colors.length]);
};

const doughnutChartConfig = {
    labels: props.doughnutLabels,
    datasets: [
        {
            data: props.doughnutData,
            backgroundColor: getColors(props.doughnutLabels?.length || 0),
            borderWidth: 2,
        }
    ]
};

const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right' }
    }
};

const showDetailModal = ref(false);
const selectedStudentDetail = ref(null);
const isLoadingDetail = ref(false);

const openDetail = async (student) => {
    selectedStudentDetail.value = student; // Pass full object so header renders immediately
    isLoadingDetail.value = true;
    showDetailModal.value = true;
    
    try {
        const response = await axios.get(route('laporan.show', student.id));
        selectedStudentDetail.value = response.data;
    } catch (error) {
        console.error("Failed to load student details", error);
    } finally {
        isLoadingDetail.value = false;
    }
};

const getCounselingBadge = (student, delayNumber) => {
    if (!student || !student.counseling_logs) return null;
    return student.counseling_logs.find(c => c.delay_count === delayNumber);
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 no-print">
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Analisa Keterlambatan</h2>
                
                <div class="flex flex-wrap items-center gap-3">
                    <select v-model="selectedPeriod" class="rounded-lg border-gray-300 text-sm font-medium focus:ring-blue-500 focus:border-blue-500 py-2">
                        <option value="this_week">Minggu Ini</option>
                        <option value="this_month">Bulan Ini</option>
                        <option value="last_month">Bulan Lalu</option>
                        <option value="this_semester">Semester Ini</option>
                    </select>

                    <a :href="route('kepala_sekolah.excel', { period: selectedPeriod })" target="_blank"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Ekspor Excel
                    </a>

                    <button @click="printPdf"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center transition-colors shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak PDF
                    </button>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-500">Menampilkan data periode: <strong>{{ periodLabel }}</strong></div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Top Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Metric 1 -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500 relative overflow-hidden">
                        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Total Siswa Terlambat</div>
                        <div class="text-4xl font-black text-gray-900 flex items-center gap-3">
                            {{ totalLates }}
                            <span v-if="momPercentage > 0" :class="['text-sm px-2 py-1 rounded font-bold flex items-center', momTrend === 'up' ? 'bg-red-100 text-red-700' : (momTrend === 'down' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')]">
                                <svg v-if="momTrend === 'up'" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                <svg v-if="momTrend === 'down'" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                                {{ momPercentage }}% dari sblmnya
                            </span>
                        </div>
                    </div>

                    <!-- Metric 2 -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-emerald-500 relative overflow-hidden">
                        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Tingkat Penanganan BK</div>
                        <div class="flex items-end gap-2">
                            <div class="text-4xl font-black text-gray-900">{{ resolutionRate }}%</div>
                            <div class="text-sm text-gray-500 mb-1">({{ resolvedLogs }} dari {{ resolvedLogs + activeLogs }} siswa ≥3× telat)</div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                            <div class="bg-emerald-500 h-2 rounded-full" :style="{ width: resolutionRate + '%' }"></div>
                        </div>
                        <div class="text-xs text-gray-400 mt-2">Siswa ≥ 3× terlambat wajib ditangani BK</div>
                    </div>

                    <!-- Metric 3 -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-amber-500 flex flex-col justify-center">
                        <div class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Kelas Terbanyak Telat</div>
                        <div class="text-2xl font-bold text-gray-900">{{ mostProblematicClass ? mostProblematicClass.name : 'N/A' }}</div>
                        <div class="text-sm text-gray-500" v-if="mostProblematicClass">{{ mostProblematicClass.total }} siswa terlambat</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Distribusi Keterlambatan per Kelas</h3>
                        <div class="h-64 relative w-full">
                            <Doughnut v-if="doughnutData?.length" :data="doughnutChartConfig" :options="doughnutChartOptions" />
                            <div v-else class="flex h-full items-center justify-center text-gray-400">Tidak ada data</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Tren Harian</h3>
                        <div class="h-64 relative w-full">
                            <Bar v-if="chartData?.length" :data="barChartConfig" :options="barChartOptions" />
                            <div v-else class="flex h-full items-center justify-center text-gray-400">Tidak ada data</div>
                        </div>
                    </div>
                </div>

                <!-- Wall of Shame -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-red-50 p-6 border-b border-red-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-black text-red-800">10 Besar Siswa Sering Terlambat</h3>
                            <p class="text-sm text-red-600 font-medium">Prioritas untuk tindak lanjut Bimbingan Konseling.</p>
                        </div>
                        <div class="hidden sm:block">
                            <svg class="w-12 h-12 text-red-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                    </div>

                    <!-- Navigation Tabs -->
                    <div class="flex items-center space-x-2 border-b border-gray-100 px-6 pt-3 bg-slate-50/50">
                        <button @click="topTab = 'active'"
                            :class="['pb-3 px-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2',
                                topTab === 'active' 
                                    ? 'border-rose-600 text-rose-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Perlu Tindakan BK</span>
                            <span v-if="activeTopStudents.length > 0" class="px-2 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-700">
                                {{ activeTopStudents.length }}
                            </span>
                        </button>

                        <button @click="topTab = 'resolved'"
                            :class="['pb-3 px-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2',
                                topTab === 'resolved' 
                                    ? 'border-emerald-600 text-emerald-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Sudah Ditindaklanjuti BK</span>
                            <span v-if="resolvedTopStudents.length > 0" class="px-2 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-700">
                                {{ resolvedTopStudents.length }}
                            </span>
                        </button>

                        <button @click="topTab = 'all'"
                            :class="['pb-3 px-3 text-sm font-bold border-b-2 transition-all flex items-center gap-2',
                                topTab === 'all' 
                                    ? 'border-blue-600 text-blue-600' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            <span>Semua 10 Besar</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-black bg-slate-200 text-slate-700">
                                {{ (wallOfShame || []).length }}
                            </span>
                        </button>
                    </div>
                    
                    <div class="overflow-x-auto p-0">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Peringkat</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Siswa</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Kelas</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Total Telat</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Status Tindak Lanjut BK</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="(student, index) in filteredWallOfShame" :key="student.id || index" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm', index < 3 && topTab === 'all' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600']">
                                            #{{ index + 1 }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ student.name }}</div>
                                        <div class="text-xs text-gray-500">{{ student.nisn }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">
                                        {{ student.class_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                                            {{ student.total_late }}x
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <!-- Kasus Tahap 3 (Telat >= 6x) -->
                                        <div v-if="student.total_late >= 6">
                                            <div v-if="isTahap3Done(student)">
                                                <div v-if="student.last_counseling_delay_count > 0 && student.total_late > student.last_counseling_delay_count">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                                        <svg class="w-3.5 h-3.5 mr-1 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                        Telat Pasca-SP (+{{ student.total_late - student.last_counseling_delay_count }}x)
                                                    </span>
                                                    <div class="text-[11px] text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block shrink-0"></span>
                                                        <span>SP oleh: <strong>{{ student.last_counselor_name }}</strong> (Telat ke-{{ student.last_counseling_delay_count }})</span>
                                                    </div>
                                                </div>
                                                <div v-else>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Konferensi Kasus / SP Selesai ({{ student.counseling_count }}x Bimbingan)
                                                    </span>
                                                    <div v-if="student.last_counselor_name" class="text-[11px] text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block shrink-0"></span>
                                                        <span>Oleh: <strong class="text-slate-700">{{ student.last_counselor_name }}</strong></span>
                                                        <span v-if="student.last_counseling_delay_count" class="text-slate-300">&bull;</span>
                                                        <span v-if="student.last_counseling_delay_count" class="text-indigo-600 bg-indigo-50 border border-indigo-100 px-1 py-0.5 rounded text-[10px] font-bold">Telat ke-{{ student.last_counseling_delay_count }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-300 animate-pulse">
                                                    <svg class="w-3.5 h-3.5 mr-1 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    Wajib Konferensi Kasus / SP (Tahap 3)
                                                </span>
                                                <div v-if="student.counseling_count > 0 && student.last_counselor_name" class="text-[11px] text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block shrink-0"></span>
                                                    <span>Tahap sebelumnya oleh: <strong>{{ student.last_counselor_name }}</strong></span>
                                                    <span class="text-slate-400">({{ student.counseling_count }}x Bimbingan)</span>
                                                </div>
                                                <div v-else class="text-[11px] text-purple-600 font-semibold mt-0.5">
                                                    Perlu Konferensi Kasus bersama Kepala Sekolah & SP
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Kasus Tahap 2 (Telat 5x) -->
                                        <div v-else-if="student.total_late >= 5">
                                            <div v-if="isTahap2Done(student)">
                                                <div v-if="student.last_counseling_delay_count > 0 && student.total_late > student.last_counseling_delay_count">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300 font-bold">
                                                        <svg class="w-3.5 h-3.5 mr-1 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                        Telat Pasca-Panggilan (+{{ student.total_late - student.last_counseling_delay_count }}x)
                                                    </span>
                                                    <div class="text-[11px] text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block shrink-0"></span>
                                                        <span>Panggilan oleh: <strong>{{ student.last_counselor_name }}</strong> (Telat ke-{{ student.last_counseling_delay_count }})</span>
                                                    </div>
                                                </div>
                                                <div v-else>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        Panggilan Ortu Selesai ({{ student.counseling_count }}x Bimbingan)
                                                    </span>
                                                    <div v-if="student.last_counselor_name" class="text-[11px] text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block shrink-0"></span>
                                                        <span>Oleh: <strong class="text-slate-700">{{ student.last_counselor_name }}</strong></span>
                                                        <span v-if="student.last_counseling_delay_count" class="text-slate-300">&bull;</span>
                                                        <span v-if="student.last_counseling_delay_count" class="text-indigo-600 bg-indigo-50 border border-indigo-100 px-1 py-0.5 rounded text-[10px] font-bold">Telat ke-{{ student.last_counseling_delay_count }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 animate-pulse">
                                                    <svg class="w-3.5 h-3.5 mr-1 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    Wajib Panggilan Orang Tua (Tahap 2)
                                                </span>
                                                <div v-if="student.counseling_count > 0 && student.last_counselor_name" class="text-[11px] text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block shrink-0"></span>
                                                    <span>Tahap sebelumnya oleh: <strong>{{ student.last_counselor_name }}</strong></span>
                                                </div>
                                                <div v-else class="text-[11px] text-rose-600 font-semibold mt-0.5">
                                                    Orang tua wajib diundang ke sekolah
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Kasus Tahap 1 (Telat 3-4x) -->
                                        <div v-else-if="student.total_late >= 3">
                                            <div v-if="isTahap1Done(student)">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    <svg class="w-3.5 h-3.5 mr-1 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Bimbingan Siswa Selesai
                                                </span>
                                                <div v-if="student.last_counselor_name" class="text-[11px] text-slate-500 font-medium mt-1 flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block shrink-0"></span>
                                                    <span>Oleh: <strong class="text-slate-700">{{ student.last_counselor_name }}</strong></span>
                                                    <span v-if="student.last_counseling_delay_count" class="text-slate-300">&bull;</span>
                                                    <span v-if="student.last_counseling_delay_count" class="text-indigo-600 bg-indigo-50 border border-indigo-100 px-1 py-0.5 rounded text-[10px] font-bold">Telat ke-{{ student.last_counseling_delay_count }}</span>
                                                </div>
                                            </div>
                                            <div v-else>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                    <svg class="w-3.5 h-3.5 mr-1 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    Wajib Bimbingan Siswa (Tahap 1)
                                                </span>
                                                <div class="text-[11px] text-amber-700 font-semibold mt-0.5">
                                                    Perlu pembinaan langsung oleh Guru BK
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Kasus Ringan (1-2x) -->
                                        <div v-else>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                Pembinaan Ringan
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button @click="openDetail(student)" class="text-blue-600 hover:text-blue-800 font-bold text-sm bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                                            Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredWallOfShame.length === 0">
                                    <td colspan="6" class="px-6 py-10 text-center">
                                        <div v-if="topTab === 'active'" class="space-y-2">
                                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 text-emerald-600">
                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <h4 class="text-base font-bold text-slate-800">Tidak Ada Kasus Menunggu Tindakan</h4>
                                            <p class="text-xs text-slate-500 max-w-md mx-auto">Semua siswa dengan keterlambatan tinggi pada periode ini telah tuntas ditindaklanjuti oleh Guru BK.</p>
                                        </div>
                                        <div v-else-if="topTab === 'resolved'" class="space-y-1">
                                            <p class="text-sm text-slate-500 font-medium">Belum ada siswa yang tercatat telah ditindaklanjuti pada periode ini.</p>
                                        </div>
                                        <div v-else class="space-y-1">
                                            <p class="text-sm text-slate-500 font-medium">Belum ada data keterlambatan pada periode ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        
        <!-- Hidden iframe for direct printing -->
        <iframe v-if="printUrl" :src="printUrl" class="absolute w-0 h-0 border-0 invisible"></iframe>

        <!-- Detail Modal -->
        <Teleport to="body">
            <div v-if="showDetailModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                    <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center sticky top-0">
                        <div class="flex items-center space-x-4">
                            <img src="/images/logo.png" alt="Logo SMK" class="h-12 w-auto object-contain">
                            <div>
                                <h3 class="text-xl font-bold text-slate-800">
                                    <span v-if="isLoadingDetail">Memuat...</span>
                                    <span v-else-if="selectedStudentDetail">{{ selectedStudentDetail.name }}</span>
                                </h3>
                                <p class="text-sm text-slate-500 font-medium">Riwayat Keterlambatan</p>
                            </div>
                        </div>
                        <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 bg-white p-2 rounded-full shadow-sm hover:shadow-md transition-all">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 overflow-y-auto flex-1 bg-slate-50/50 space-y-6">
                        <div v-if="isLoadingDetail" class="text-center py-10">
                            <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </div>
                        <template v-else-if="selectedStudentDetail">
                            <!-- Riwayat Tindak Lanjut Guru BK -->
                            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-800 text-sm">Status & Rekam Bimbingan Konseling (BK)</h4>
                                            <p class="text-xs text-slate-500">Hasil penanganan dan intervensi kedisiplinan siswa</p>
                                        </div>
                                    </div>
                                    <span v-if="selectedStudentDetail.counseling_logs && selectedStudentDetail.counseling_logs.length > 0"
                                        class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold">
                                        {{ selectedStudentDetail.counseling_logs.length }}x Ditindaklanjuti
                                    </span>
                                    <span v-else-if="(selectedStudentDetail.delay_logs && selectedStudentDetail.delay_logs.length >= 3) || selectedStudentDetail.total_late >= 3"
                                        class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-full text-xs font-bold">
                                        Belum Ditindaklanjuti
                                    </span>
                                    <span v-else
                                        class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium">
                                        Pembinaan Ringan
                                    </span>
                                </div>

                                <div v-if="selectedStudentDetail.counseling_logs && selectedStudentDetail.counseling_logs.length > 0" class="space-y-3">
                                    <div v-for="cLog in selectedStudentDetail.counseling_logs" :key="cLog.id"
                                        class="p-4 bg-indigo-50/70 rounded-xl border border-indigo-100 text-sm space-y-1.5">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="flex items-center gap-2 font-bold text-indigo-900 text-xs">
                                                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    {{ cLog.stage || 'Bimbingan Konseling' }}
                                                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded-full">
                                                        Ditindak saat Telat ke-{{ cLog.delay_count || '?' }}
                                                    </span>
                                                </div>
                                                <div class="text-[11px] text-slate-500 font-medium mt-0.5 ml-6">
                                                    Guru BK: <strong>{{ cLog.counselor_name || cLog.user?.name || 'Guru BK' }}</strong>
                                                </div>
                                            </div>
                                            <span class="text-xs text-slate-500 font-medium bg-white px-2 py-0.5 rounded-md border border-slate-200">
                                                {{ new Date(cLog.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                            </span>
                                        </div>
                                        <p class="text-slate-700 text-xs leading-relaxed whitespace-pre-wrap pl-6 border-l-2 border-indigo-300">{{ cLog.notes }}</p>
                                    </div>
                                </div>
                                <div v-else-if="(selectedStudentDetail.delay_logs && selectedStudentDetail.delay_logs.length >= 3) || selectedStudentDetail.total_late >= 3"
                                    class="p-4 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-3">
                                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div>
                                        <p class="text-xs font-bold text-rose-800">Perhatian: Belum Ada Catatan Konseling BK</p>
                                        <p class="text-xs text-rose-600 mt-0.5">Siswa telah melampaui batas toleransi keterlambatan dan memerlukan tindakan langsung dari pihak Guru BK.</p>
                                    </div>
                                </div>
                                <div v-else class="text-xs text-slate-500 italic py-1">
                                    Siswa belum memerlukan bimbingan khusus dari Guru BK.
                                </div>
                            </div>

                            <!-- Riwayat Detail Keterlambatan -->
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm mb-3">Timeline Riwayat Keterlambatan</h4>
                                <div v-if="selectedStudentDetail.delay_logs && selectedStudentDetail.delay_logs.length > 0" class="space-y-4 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
                                    <div v-for="(log, logIdx) in selectedStudentDetail.delay_logs" :key="log.id" class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                        <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white bg-blue-100 text-blue-600 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10 font-black text-xs">
                                            {{ selectedStudentDetail.delay_logs.length - logIdx }}
                                        </div>
                                        <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                                            <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-1">
                                                <div class="font-bold text-slate-800">{{ new Date(log.delay_time).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</div>
                                                <div class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ new Date(log.delay_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</div>
                                            </div>
                                            <div class="text-slate-600 text-sm">Alasan: <strong>{{ log.reason }}</strong></div>
                                            <div class="text-slate-500 text-xs mt-1 font-medium italic" v-if="log.reporter_name">Petugas: {{ log.reporter_name }}</div>

                                            <!-- Indikator Tindak Lanjut BK -->
                                            <div v-if="getCounselingBadge(selectedStudentDetail, selectedStudentDetail.delay_logs.length - logIdx)"
                                                class="mt-3 pt-2.5 border-t border-indigo-100 bg-indigo-50/60 p-2.5 rounded-lg space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="inline-flex items-center text-xs font-black text-indigo-800">
                                                        <svg class="w-3.5 h-3.5 mr-1 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ getCounselingBadge(selectedStudentDetail, selectedStudentDetail.delay_logs.length - logIdx).stage || 'Ditindaklanjuti BK' }}
                                                    </span>
                                                    <span class="text-[11px] text-slate-500 font-medium">
                                                        {{ new Date(getCounselingBadge(selectedStudentDetail, selectedStudentDetail.delay_logs.length - logIdx).created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}
                                                    </span>
                                                </div>
                                                <div class="text-[11px] text-indigo-950 italic line-clamp-2">
                                                    "{{ getCounselingBadge(selectedStudentDetail, selectedStudentDetail.delay_logs.length - logIdx).notes }}"
                                                </div>
                                                <div class="text-[10px] text-indigo-600 font-bold">
                                                    Oleh: {{ getCounselingBadge(selectedStudentDetail, selectedStudentDetail.delay_logs.length - logIdx).counselor_name || 'Guru BK' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-6 text-gray-500 text-xs">
                                    Tidak ada data keterlambatan yang valid untuk ditampilkan.
                                </div>
                            </div>
                        </template>
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
    animation: fade-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
