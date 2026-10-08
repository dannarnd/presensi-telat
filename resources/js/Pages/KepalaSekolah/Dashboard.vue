<script setup>
import { ref, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Bar, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

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
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 no-print">
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Analisa Keterlambatan</h2>
                
                <div class="flex flex-wrap items-center gap-3">
                    <select v-model="selectedPeriod" class="rounded-lg border-gray-300 text-sm font-medium focus:ring-blue-500 focus:border-blue-500 py-2">
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
                    
                    <div class="overflow-x-auto p-0">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Peringkat</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Siswa</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Kelas</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Total Telat</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="(student, index) in wallOfShame" :key="index" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm', index < 3 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600']">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <Link :href="route('laporan.index', { search: student.nisn })" class="text-blue-600 hover:text-blue-800 font-bold text-sm bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                                            Lihat Detail
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="wallOfShame.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        Belum ada data keterlambatan pada periode ini.
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
    </AuthenticatedLayout>
</template>
