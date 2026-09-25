<script setup>
import { ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    students: Array,
    classes: Array,
    filters: Object,
});

const page = usePage();
const selectedClassId = ref(props.filters.class_id || '');
const searchQuery = ref(props.filters.search || '');

let searchTimeout;
watch([selectedClassId, searchQuery], ([newClass, newSearch]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('laporan.index'), {
            class_id: newClass,
            search: newSearch
        }, {
            preserveState: true,
            replace: true
        });
    }, 300);
});

const showDetailModal = ref(false);
const selectedStudent = ref(null);
const isLoadingDetail = ref(false);

const printUrl = ref('');
const printPdf = () => {
    printUrl.value = route('laporan.pdf', { class_id: selectedClassId.value, search: searchQuery.value, t: Date.now() });
};

const openDetail = async (student) => {
    selectedStudent.value = student;
    showDetailModal.value = true;
    isLoadingDetail.value = true;
    
    try {
        const res = await axios.get(route('laporan.show', student.id));
        selectedStudent.value = res.data;
    } catch (error) {
        console.error("Gagal mengambil detail", error);
    } finally {
        isLoadingDetail.value = false;
    }
};

const getWarningBadge = (count) => {
    if (count >= 5) {
        return { class: 'bg-red-100 text-red-800 border-red-200', text: 'Sangat Sering (Panggil Ortu)' };
    } else if (count >= 3) {
        return { class: 'bg-orange-100 text-orange-800 border-orange-200', text: 'Sering (Peringatan)' };
    } else if (count > 0) {
        return { class: 'bg-yellow-100 text-yellow-800 border-yellow-200', text: 'Pernah Telat' };
    }
    return { class: 'bg-green-100 text-green-800 border-green-200', text: 'Disiplin (Nihil)' };
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">Riwayat Keterlambatan</h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter and Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-center bg-white p-4 rounded-2xl shadow-sm border border-slate-100 gap-4 mb-6">
                <div class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                    <div class="w-full sm:w-auto">
                        <label class="text-sm font-bold text-slate-600 block mb-1">Cari Siswa:</label>
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            placeholder="Ketik Nama / NISN..." 
                            class="w-full sm:w-64 rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 font-medium text-slate-700"
                        >
                    </div>
                    <div class="w-full sm:w-auto">
                        <label class="text-sm font-bold text-slate-600 block mb-1">Filter Kelas:</label>
                        <select v-model="selectedClassId" class="w-full sm:w-64 rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 font-medium text-slate-700">
                            <option value="">-- Semua Kelas --</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="w-full sm:w-auto flex justify-end">
                    <button @click="printPdf"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-bold flex items-center transition-colors shadow-sm mt-4 sm:mt-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak PDF
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-hidden shadow-lg shadow-slate-200/50 rounded-2xl border border-slate-100 flex flex-col min-h-[500px]">
                <div class="overflow-auto flex-1 p-0">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">NISN</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Lengkap</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Kelas</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Total Telat</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr v-for="student in students" :key="student.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">{{ student.nisn }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">{{ student.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">{{ student.school_class?.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="text-2xl font-black text-slate-800 mb-1" title="Total keterlambatan aktif">{{ student.active_delay_logs_count }}</div>
                                        <div class="text-xs text-slate-500 mb-1">Total Riwayat: {{ student.delay_logs_count }}</div>
                                        <span :class="['px-2 py-0.5 inline-flex text-xs font-bold rounded-full border', getWarningBadge(student.active_delay_logs_count).class]">
                                            {{ getWarningBadge(student.active_delay_logs_count).text }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button 
                                        @click="openDetail(student)" 
                                        class="text-blue-600 hover:text-blue-900 font-bold text-sm bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg transition-colors flex items-center justify-center mx-auto"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        Lihat Rincian
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="students.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    <div class="inline-flex justify-center items-center w-12 h-12 rounded-full bg-slate-100 mb-3">
                                        <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    </div>
                                    <p>Tidak ada data siswa ditemukan.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <Teleport to="body">
            <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                    <!-- Header -->
                    <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center sticky top-0">
                        <div class="flex items-center space-x-4">
                            <img src="/images/logo.png" alt="Logo SMK" class="h-12 w-auto object-contain">
                            <div>
                                <h3 class="text-xl font-bold text-slate-800">{{ selectedStudent?.name }}</h3>
                                <p class="text-sm text-slate-500 font-medium">{{ selectedStudent?.nisn }} &bull; Kelas {{ selectedStudent?.school_class?.name }}</p>
                            </div>
                        </div>
                        <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 bg-white p-2 rounded-full shadow-sm hover:shadow-md transition-all">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6 overflow-y-auto flex-1 bg-slate-50/50">
                        <div v-if="isLoadingDetail" class="text-center py-12">
                            <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <p class="mt-4 text-slate-500 font-medium">Memuat riwayat...</p>
                        </div>
                        <div v-else>
                            <div v-if="!selectedStudent.delay_logs || selectedStudent.delay_logs.length === 0" class="text-center py-12 bg-white rounded-xl border border-dashed border-slate-200">
                                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <h4 class="text-lg font-bold text-slate-800">Siswa Teladan!</h4>
                                <p class="text-slate-500">Siswa ini tidak memiliki riwayat keterlambatan sama sekali.</p>
                            </div>
                            <div v-else class="space-y-4 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
                                <!-- Timeline items -->
                                <div v-for="(log, index) in selectedStudent.delay_logs" :key="log.id" class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                    
                                    <!-- Icon -->
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white bg-blue-100 text-blue-600 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    
                                    <!-- Card -->
                                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                                        <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-1">
                                            <div class="font-bold text-slate-800">{{ new Date(log.delay_time).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</div>
                                            <div class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ new Date(log.delay_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</div>
                                        </div>
                                        <div class="text-slate-600 text-sm">Alasan: <strong>{{ log.reason }}</strong></div>
                                        <div class="text-slate-500 text-xs mt-1 font-medium italic" v-if="log.reporter_name">Petugas: {{ log.reporter_name }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Hidden iframe for direct printing -->
        <iframe v-if="printUrl" :src="printUrl" class="absolute w-0 h-0 border-0 invisible"></iframe>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(10px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-fade-in {
    animation: fade-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
