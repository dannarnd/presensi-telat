<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
    todayLogs: Array,
    classes: Array,
    teachers: Array,
});

const page = usePage();

// Tabs state
const activeTab = ref('search'); // 'search' or 'browse'

// Search state
const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);

// Fitur Tiket Rombongan (Per Kelas)
const showClassTicketModal = ref(false);
const viewingClassTicket = ref(null);

const todayLogsGroupedByClass = computed(() => {
    const groups = {};
    props.todayLogs.forEach(log => {
        const className = log.student?.school_class?.name || 'Lainnya';
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

const openClassTicket = (group) => {
    viewingClassTicket.value = group;
    showClassTicketModal.value = false;
};

// Browse state
const selectedClassId = ref('');
const classStudents = ref([]);
const classSearchQuery = ref(''); // search within class
const classPickerSearch = ref(''); // search in class picker list
const isLoadingStudents = ref(false);

// Filtered classes for class picker
const filteredClasses = computed(() => {
    if (!classPickerSearch.value) return props.classes;
    const q = classPickerSearch.value.toLowerCase();
    return props.classes.filter(c => c.name.toLowerCase().includes(q));
});

// Filtered students within selected class
const filteredClassStudents = computed(() => {
    if (!classSearchQuery.value) return classStudents.value;
    const q = classSearchQuery.value.toLowerCase();
    return classStudents.value.filter(s => s.name.toLowerCase().includes(q) || s.nisn?.toLowerCase().includes(q));
});

// General selection
const selectedStudent = ref(null);

// Form reasons
const reasons = [
    'Kesiangan',
    'Macet',
    'Kendaraan Rusak / Ban Kempes',
    'Hujan / Cuaca Buruk',
    'Jarak Rumah Jauh',
    'Telat Bangun',
    'Izin Urusan Keluarga',
    'Sakit',
];
const showManualReason = ref(false);

const form = useForm({
    student_id: '',
    reason: '',
    reporter_name: '',
});

// Watch Search Mode
let searchTimeout;
watch(searchQuery, (newVal) => {
    clearTimeout(searchTimeout);
    if (!newVal) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    searchTimeout = setTimeout(async () => {
        try {
            const res = await axios.get(route('guru_piket.search'), { params: { q: newVal } });
            searchResults.value = res.data;
        } catch (error) {
            console.error(error);
        } finally {
            isSearching.value = false;
        }
    }, 300);
});

// Watch Browse Mode
watch(selectedClassId, async (newVal) => {
    if (!newVal) {
        classStudents.value = [];
        classSearchQuery.value = '';
        return;
    }
    classSearchQuery.value = ''; // reset filter when class changes
    isLoadingStudents.value = true;
    try {
        const res = await axios.get(route('guru_piket.students_by_class'), { params: { class_id: newVal } });
        classStudents.value = res.data;
    } catch (error) {
        console.error(error);
    } finally {
        isLoadingStudents.value = false;
    }
});

const selectStudent = (student) => {
    selectedStudent.value = student;
    form.student_id = student.id;
    form.clearErrors();
    // Clear search/browse states to look clean
    searchQuery.value = '';
    searchResults.value = [];
};

const selectReason = (reason) => {
    form.reason = reason;
    showManualReason.value = false;
};

const toggleManualReason = () => {
    form.reason = '';
    showManualReason.value = true;
};

const submitForm = () => {
    form.post(route('guru_piket.store'), {
        preserveScroll: true,
        onSuccess: () => {
            resetForm();
        }
    });
};

const resetForm = () => {
    selectedStudent.value = null;
    form.reset();
    showManualReason.value = false;
};

const closeTicket = () => {
    // We clear the flash ticket by reloading
    router.reload({ only: ['flash'] });
    viewingTicket.value = null;
};

const getWarningBadge = (count) => {
    if (count >= 5) {
        return { class: 'bg-red-100 text-red-800 border-red-200', text: 'Panggilan Ortu (≥5)' };
    } else if (count >= 3) {
        return { class: 'bg-orange-100 text-orange-800 border-orange-200', text: 'Peringatan (≥3)' };
    }
    return { class: 'bg-green-100 text-green-800 border-green-200', text: 'Aman' };
};

// Viewing past ticket
const viewingTicket = ref(null);
const viewPastTicket = (log) => {
    viewingTicket.value = log;
};

// Search Log Hari Ini
const logSearchQuery = ref('');
const filteredTodayLogs = computed(() => {
    if (!logSearchQuery.value) return props.todayLogs;
    const q = logSearchQuery.value.toLowerCase();
    return props.todayLogs.filter(log =>
        log.student?.name.toLowerCase().includes(q) ||
        log.student?.nisn.toLowerCase().includes(q) ||
        log.student?.school_class?.name.toLowerCase().includes(q)
    );
});

// Edit Log
const showEditLogModal = ref(false);
const logToEdit = ref(null);
const editLogForm = useForm({
    student_id: '',
    reason: '',
});

const openEditLog = (log) => {
    logToEdit.value = log;
    editLogForm.student_id = log.student_id;
    editLogForm.reason = log.reason;
    showEditLogModal.value = true;
};

const submitEditLog = () => {
    editLogForm.put(route('guru_piket.update_log', logToEdit.value.id), {
        onSuccess: () => {
            showEditLogModal.value = false;
        }
    });
};

// Delete Log
const showDeleteLogModal = ref(false);
const logToDelete = ref(null);
const deleteLogForm = useForm({});

const openDeleteLog = (log) => {
    logToDelete.value = log;
    showDeleteLogModal.value = true;
};

const submitDeleteLog = () => {
    deleteLogForm.delete(route('guru_piket.destroy_log', logToDelete.value.id), {
        onSuccess: () => {
            showDeleteLogModal.value = false;
        }
    });
};

</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">Terminal Guru Piket</h2>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <!-- Input Panel (Left) -->
                <div class="lg:col-span-5">
                    <div
                        class="bg-white rounded-2xl shadow-xl shadow-blue-100/50 border border-blue-50 p-6 sticky top-8">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-800">Input Keterlambatan</h3>
                                <p class="text-xs text-slate-500">Pilih metode pencarian siswa</p>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="flex space-x-2 mb-6 bg-slate-100 p-1 rounded-xl" v-if="!selectedStudent">
                            <button @click="activeTab = 'search'"
                                :class="['flex-1 py-2 text-sm font-bold rounded-lg transition-all', activeTab === 'search' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700']">
                                Ketik Nama
                            </button>
                            <button @click="activeTab = 'browse'"
                                :class="['flex-1 py-2 text-sm font-bold rounded-lg transition-all', activeTab === 'browse' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-700']">
                                Pilih per Kelas
                            </button>
                        </div>

                        <!-- Mode 1: Smart Search -->
                        <div v-if="activeTab === 'search' && !selectedStudent" class="mb-5 animate-fade-in">
                            <div class="relative">
                                <input type="text" v-model="searchQuery" placeholder="Ketik nama atau NIS siswa..."
                                    class="w-full border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-base p-4 pl-12 transition-colors">
                                <svg class="w-6 h-6 text-slate-400 absolute left-4 top-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <!-- Search Results (inline, not absolute to avoid overlap) -->
                            <div v-if="searchResults.length > 0"
                                class="mt-2 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                                <ul class="max-h-60 overflow-auto divide-y divide-slate-100">
                                    <li v-for="student in searchResults" :key="student.id"
                                        @click="selectStudent(student)"
                                        class="cursor-pointer hover:bg-blue-50 px-4 py-3 transition-colors flex justify-between items-center">
                                        <div>
                                            <div class="font-bold text-slate-900">{{ student.name }}</div>
                                            <div class="text-xs font-medium text-slate-500">{{ student.nisn }}</div>
                                        </div>
                                        <div
                                            class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs font-bold shrink-0 ml-2">
                                            {{ student.school_class?.name }}</div>
                                    </li>
                                </ul>
                            </div>
                            <div v-else-if="searchQuery && !isSearching"
                                class="mt-2 text-sm text-center text-slate-400 py-3">
                                Siswa tidak ditemukan.
                            </div>
                        </div>

                        <!-- Mode 2: Browse by Class -->
                        <div v-if="activeTab === 'browse' && !selectedStudent" class="mb-5 space-y-3 animate-fade-in">
                            <!-- Custom class picker (no native select to avoid overflow) -->
                            <div v-if="!selectedClassId">
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Kelas</label>
                                <div class="relative mb-2">
                                    <input type="text" v-model="classPickerSearch" placeholder="Cari kelas..."
                                        class="w-full border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm p-3 pl-10 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <div class="border border-slate-200 rounded-xl overflow-hidden">
                                    <ul class="max-h-56 overflow-auto divide-y divide-slate-100">
                                        <li v-if="filteredClasses.length === 0"
                                            class="px-4 py-4 text-sm text-center text-slate-400">
                                            Kelas tidak ditemukan.
                                        </li>
                                        <li v-for="cls in filteredClasses" :key="cls.id"
                                            @click="selectedClassId = cls.id"
                                            class="cursor-pointer hover:bg-blue-50 active:bg-blue-100 px-4 py-3 transition-colors flex items-center justify-between">
                                            <span class="font-semibold text-slate-800 text-sm">{{ cls.name }}</span>
                                            <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Selected class header (shown after picking) -->
                            <div v-if="selectedClassId"
                                class="flex items-center justify-between bg-blue-50 border border-blue-100 rounded-xl px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span class="font-bold text-blue-800 text-sm">{{classes.find(c => c.id ==
                                        selectedClassId)?.name}}</span>
                                </div>
                                <button @click="selectedClassId = ''; classSearchQuery = ''; classPickerSearch = ''"
                                    class="text-blue-400 hover:text-red-500 transition-colors text-xs font-medium">
                                    Ganti Kelas
                                </button>
                            </div>

                            <!-- Student list with search (only after class selected) -->
                            <div v-if="selectedClassId">
                                <div v-if="isLoadingStudents" class="text-sm text-slate-500 italic p-3 text-center">
                                    Memuat
                                    siswa...</div>
                                <div v-else>
                                    <!-- Search within class -->
                                    <div class="relative mb-2">
                                        <input type="text" v-model="classSearchQuery"
                                            placeholder="Cari nama siswa di kelas ini..."
                                            class="w-full border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm text-sm p-3 pl-10 transition-colors">
                                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <!-- Scrollable student list -->
                                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                                        <ul class="max-h-52 overflow-auto divide-y divide-slate-100">
                                            <li v-if="filteredClassStudents.length === 0"
                                                class="px-4 py-4 text-sm text-center text-slate-400">
                                                Tidak ada siswa ditemukan.
                                            </li>
                                            <li v-for="student in filteredClassStudents" :key="student.id"
                                                @click="selectStudent(student)"
                                                class="cursor-pointer hover:bg-blue-50 active:bg-blue-100 px-4 py-3 transition-colors flex items-center justify-between">
                                                <div>
                                                    <div class="font-semibold text-slate-800 text-sm">{{ student.name }}
                                                    </div>
                                                    <div class="text-xs text-slate-400">{{ student.nisn }}</div>
                                                </div>
                                                <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </li>
                                        </ul>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1 text-right">{{ filteredClassStudents.length }}
                                        siswa
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Student Indicator -->
                        <div v-if="selectedStudent"
                            class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-blue-50 border border-blue-100 rounded-xl flex justify-between items-center animate-fade-in shadow-sm">
                            <div>
                                <div class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-1">Siswa
                                    Terpilih</div>
                                <div class="font-black text-blue-900 text-xl leading-none">{{ selectedStudent.name }}
                                </div>
                                <div class="text-blue-700 text-sm font-medium mt-1">{{ selectedStudent.nisn }} &bull;
                                    Kelas {{
                                        selectedStudent.school_class?.name }}</div>
                            </div>
                            <button @click="resetForm"
                                class="h-10 w-10 rounded-full bg-white text-blue-400 hover:text-red-500 shadow-sm flex items-center justify-center transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- 2. Quick-Tap Reasons -->
                        <div v-if="selectedStudent" class="mb-8 animate-fade-in">
                            <label class="block text-sm font-semibold text-slate-700 mb-3">Alasan Keterlambatan</label>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="reason in reasons" :key="reason" type="button"
                                    @click="selectReason(reason)" :class="[
                                        'px-4 py-2.5 rounded-lg border font-bold text-sm transition-all',
                                        form.reason === reason && !showManualReason
                                            ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200 scale-105'
                                            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300'
                                    ]">
                                    {{ reason }}
                                </button>
                                <button type="button" @click="toggleManualReason" :class="[
                                    'px-4 py-2.5 rounded-lg border font-bold text-sm transition-all',
                                    showManualReason
                                        ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200 scale-105'
                                        : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-slate-300'
                                ]">
                                    Lainnya...
                                </button>
                            </div>

                            <div v-if="showManualReason" class="mt-4 animate-fade-in">
                                <input type="text" v-model="form.reason" placeholder="Ketik alasan spesifik..."
                                    class="w-full border-slate-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm"
                                    autofocus>
                            </div>
                        </div>

                        <!-- Nama Guru Piket (Opsional) -->
                        <div v-if="selectedStudent" class="mb-8 animate-fade-in">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Guru Piket <span
                                    class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input type="text" v-model="form.reporter_name" placeholder="Nama guru yang bertugas piket"
                                class="w-full border-slate-300 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                        </div>

                        <!-- 3. Submit Button -->
                        <div v-if="form.errors.student_id"
                            class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-bold text-red-700">{{ form.errors.student_id }}</p>
                                </div>
                            </div>
                        </div>

                        <button v-if="selectedStudent" @click="submitForm" :disabled="form.processing || !form.reason"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-600 text-white font-black text-lg py-4 rounded-xl shadow-lg shadow-blue-200 hover:from-blue-700 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all active:scale-95 flex justify-center items-center">
                            {{ form.processing ? 'Memproses...' : 'SIMPAN' }}
                        </button>
                    </div>
                </div>

                <!-- Log Panel (Right) -->
                <div class="lg:col-span-7">
                    <div
                        class="bg-white overflow-hidden shadow-sm border border-slate-200 rounded-2xl h-full flex flex-col">
                        <div
                            class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between md:items-center bg-slate-50 gap-4">
                            <div>
                                <h3 class="font-bold text-xl text-slate-800">Log Hari Ini</h3>
                                <p class="text-sm text-slate-500 mt-1">Daftar siswa terlambat real-time</p>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center space-y-3 sm:space-y-0 sm:space-x-3">
                                <button @click="showClassTicketModal = true"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-lg text-sm shadow-sm transition-colors flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Cetak Tiket Kelas
                                </button>
                                <div class="relative">
                                    <input type="text" v-model="logSearchQuery" placeholder="Cari siswa/kelas..."
                                        class="w-full sm:w-64 text-sm border-slate-200 bg-white focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm py-2 pl-9 pr-3 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <div
                                    class="bg-blue-100 text-blue-700 font-bold px-3 py-1.5 rounded-lg text-sm whitespace-nowrap shadow-sm">
                                    Jumlah: {{ filteredTodayLogs.length }} Siswa
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto flex-1 p-0">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-white sticky top-0 z-10 shadow-sm">
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                            Siswa & Kelas</th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                            Waktu & Status</th>
                                        <th
                                            class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    <tr v-for="log in filteredTodayLogs" :key="log.id"
                                        class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-slate-900">{{ log.student?.name }}</div>
                                            <div class="text-sm font-medium text-slate-500">{{
                                                log.student?.school_class?.name
                                            }} &bull; {{ log.reason }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-slate-900 mb-1">{{ new
                                                Date(log.delay_time).toLocaleTimeString('id-ID', {
                                                    hour: '2-digit',
                                                    minute:
                                                        '2-digit'
                                                }) }}</div>
                                            <span
                                                :class="['px-2 py-0.5 inline-flex text-[10px] leading-5 font-bold rounded border', getWarningBadge(log.student?.active_delay_logs_count).class]">
                                                {{ getWarningBadge(log.student?.active_delay_logs_count).text }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                                            <button @click="viewPastTicket(log)"
                                                class="text-blue-600 hover:text-blue-900 font-bold text-sm bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors">
                                                Tiket
                                            </button>
                                            <button @click="openEditLog(log)"
                                                class="text-yellow-600 hover:text-yellow-900 font-bold text-sm bg-yellow-50 hover:bg-yellow-100 px-3 py-1.5 rounded-lg transition-colors">
                                                Edit
                                            </button>
                                            <button @click="openDeleteLog(log)"
                                                class="text-red-600 hover:text-red-900 font-bold text-sm bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredTodayLogs.length === 0">
                                        <td colspan="3" class="px-6 py-12 text-center">
                                            <div
                                                class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                                                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </div>
                                            <p class="text-slate-500 font-medium">
                                                {{ logSearchQuery ? 'Pencarian tidak ditemukan.' : 'Belum ada siswa terlambat hari ini. Luar biasa!' }}
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Digital Ticket Modal -->
        <Teleport to="body">
            <div v-if="$page.props.flash.ticket || viewingTicket"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md">
                <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl animate-bounce-in relative">
                    <!-- Header -->
                    <div class="bg-red-600 p-6 text-center text-white relative overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-red-500 rounded-full opacity-50"></div>
                        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-red-700 rounded-full opacity-50"></div>
                        <h2 class="text-3xl font-black tracking-widest uppercase relative z-10">TIKET MASUK</h2>
                        <p class="text-red-100 font-medium mt-1 relative z-10">Izin Keterlambatan</p>
                    </div>

                    <!-- Ticket Content -->
                    <div class="p-5 md:p-6">
                        <div class="text-center mb-4 flex flex-col items-center justify-center">
                            <img src="/images/logo.png" alt="Logo SMK" class="h-14 w-auto object-contain mb-2">
                            <div class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">SMK Negeri 5
                                Telkom
                                Banda Aceh</div>
                        </div>

                        <div class="space-y-3">
                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Siswa
                                </div>
                                <div class="text-lg font-black text-slate-900 leading-tight">{{
                                    ($page.props.flash.ticket ||
                                        viewingTicket).student?.name }}</div>
                                <div class="text-xs font-medium text-slate-500">{{ ($page.props.flash.ticket ||
                                    viewingTicket).student?.nisn }} &bull; Kelas {{ ($page.props.flash.ticket ||
                                        viewingTicket).student?.school_class?.name }}</div>
                            </div>

                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">
                                    Tanggal
                                </div>
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ new Date(($page.props.flash.ticket ||
                                        viewingTicket).delay_time).toLocaleDateString('id-ID', {
                                            day: 'numeric', month:
                                                'short',
                                            year: 'numeric'
                                        }) }}
                                </div>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">
                                    Alasan
                                </div>
                                <div class="font-bold text-slate-800 text-sm break-words whitespace-normal leading-snug"
                                    :title="($page.props.flash.ticket || viewingTicket).reason">
                                    {{ ($page.props.flash.ticket || viewingTicket).reason }}
                                </div>
                            </div>

                            <!-- Signature & Stamp -->
                            <div
                                class="mt-4 border-t border-dashed border-slate-200 pt-4 flex justify-between items-end">
                                <div class="text-left">
                                    <div
                                        class="w-12 h-12 border-[3px] border-red-500/30 rounded-full flex items-center justify-center transform -rotate-12 relative">
                                        <span
                                            class="text-red-500/50 font-black text-[8px] uppercase transform rotate-12 text-center leading-tight tracking-widest">SMKN
                                            5<br>VALID</span>
                                    </div>
                                </div>
                                <div class="text-right flex flex-col items-end">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-4">Guru
                                        Piket
                                    </div>
                                    <div
                                        class="font-bold text-slate-800 border-b-2 border-slate-800 pb-0.5 inline-block text-sm">
                                        {{ ($page.props.flash.ticket || viewingTicket).reporter_name || 'Guru Piket' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <span class="text-[8px] text-slate-300 font-bold tracking-[0.2em] uppercase">PPKPMUINARRANIRY2026</span>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="p-6 bg-white border-t border-slate-100">
                        <button @click="closeTicket"
                            class="w-full bg-slate-900 text-white font-black text-lg py-4 rounded-xl hover:bg-slate-800 transition-colors shadow-xl shadow-slate-200">
                            TUTUP
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Edit Log Modal -->
        <Teleport to="body">
            <div v-if="showEditLogModal"
            class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                <h3 class="text-xl font-bold text-slate-800 mb-4">Edit Log Keterlambatan</h3>
                <p class="text-sm text-slate-500 mb-4">Siswa: <strong>{{ logToEdit?.student?.name }}</strong>. Jika Anda
                    salah
                    memilih siswa, silakan Hapus log ini dan buat baru.</p>
                <form @submit.prevent="submitEditLog" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Alasan Keterlambatan</label>
                        <input type="text" v-model="editLogForm.reason"
                            class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
                            required>
                        <p v-if="editLogForm.errors.reason" class="mt-1 text-sm text-red-600">{{
                            editLogForm.errors.reason }}
                        </p>
                    </div>
                    <div class="pt-4 flex justify-end space-x-3">
                        <button type="button" @click="showEditLogModal = false"
                            class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                        <button type="submit" :disabled="editLogForm.processing"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 disabled:opacity-50">Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Log Modal -->
        <div v-if="showDeleteLogModal"
            class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
            <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl p-6 text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800 mb-2">Hapus Log Keterlambatan?</h3>
                <p class="text-sm text-slate-500 mb-6">Log atas nama <strong>{{ logToDelete?.student?.name }}</strong>
                    akan
                    dihapus permanen. Tindakan ini tidak bisa dibatalkan.</p>

                <div class="flex justify-center space-x-3">
                    <button @click="showDeleteLogModal = false"
                        class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                    <button @click="submitDeleteLog" :disabled="deleteLogForm.processing"
                        class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-red-700 disabled:opacity-50 transition-colors">Ya,
                        Hapus</button>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- 5. Class List Modal for Tickets -->
        <Teleport to="body">
            <div v-if="showClassTicketModal"
                class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl flex flex-col max-h-[80vh]">
                    <div class="bg-slate-50 p-4 border-b border-slate-100 flex justify-between items-center sticky top-0">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Tiket Rombongan Kelas</h3>
                            <p class="text-xs text-slate-500">Pilih kelas untuk melihat tiket hari ini</p>
                        </div>
                        <button @click="showClassTicketModal = false"
                            class="text-slate-400 hover:text-slate-600 bg-white p-2 rounded-full shadow-sm hover:shadow-md transition-all">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-4 overflow-y-auto flex-1 bg-white space-y-3">
                        <div v-if="todayLogsGroupedByClass.length === 0" class="text-center py-8 text-slate-500 text-sm font-medium">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 mb-3">
                                <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <p>Belum ada keterlambatan hari ini.</p>
                        </div>
                        <div v-for="group in todayLogsGroupedByClass" :key="group.class_name" 
                            class="flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-blue-50 transition-colors">
                            <div>
                                <div class="font-bold text-slate-800">{{ group.class_name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ group.logs.length }} Siswa terlambat</div>
                            </div>
                            <button @click="openClassTicket(group)" 
                                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition-colors shadow-sm flex items-center">
                                Buka Tiket
                                <svg class="w-3 h-3 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- 6. Digital Class Ticket Modal -->
        <Teleport to="body">
            <div v-if="viewingClassTicket"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md">
                <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl animate-bounce-in relative flex flex-col max-h-[90vh]">
                    <!-- Header -->
                    <div class="bg-blue-600 p-6 text-center text-white relative overflow-hidden flex-shrink-0">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-blue-500 rounded-full opacity-50"></div>
                        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-700 rounded-full opacity-50"></div>
                        <h2 class="text-2xl font-black tracking-widest uppercase relative z-10">TIKET ROMBONGAN</h2>
                        <p class="text-blue-100 font-medium mt-1 relative z-10">Izin Masuk Kelas</p>
                    </div>

                    <!-- Ticket Content -->
                    <div class="p-5 md:p-6 overflow-y-auto flex-1">
                        <div class="text-center mb-4 flex flex-col items-center justify-center">
                            <img src="/images/logo.png" alt="Logo SMK" class="h-12 w-auto object-contain mb-2">
                            <div class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">SMKN 5 Telkom Banda Aceh</div>
                        </div>

                        <div class="space-y-3 mb-5">
                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-center shadow-inner">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Kelas</div>
                                <div class="text-xl font-black text-slate-800">{{ viewingClassTicket.class_name }}</div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Tanggal</div>
                                    <div class="text-sm font-bold text-slate-800">
                                        {{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                    </div>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Petugas Piket</div>
                                    <div class="text-sm font-bold text-slate-800 line-clamp-1" :title="$page.props.auth.user.name">{{ $page.props.auth.user.name }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t-2 border-dashed border-slate-200 pt-4 mb-2">
                            <div class="flex justify-between items-end mb-3">
                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Daftar Siswa</div>
                                <div class="text-[11px] font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ viewingClassTicket.logs.length }} Anak</div>
                            </div>
                            <div class="space-y-2.5">
                                <div v-for="(log, idx) in viewingClassTicket.logs" :key="log.id" class="flex justify-between items-start text-sm bg-white border border-slate-50 p-2 rounded-lg">
                                    <div class="flex space-x-2">
                                        <span class="font-bold text-slate-400 w-4">{{ idx + 1 }}.</span>
                                        <div>
                                            <div class="font-bold text-slate-800 leading-tight">{{ log.student?.name }}</div>
                                            <div class="text-[10px] font-medium text-slate-500 mt-0.5">Alasan: {{ log.reason }}</div>
                                        </div>
                                    </div>
                                    <div class="font-mono text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded shadow-sm border border-blue-100 shrink-0">
                                        {{ new Date(log.delay_time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-slate-50 p-4 border-t border-slate-100 text-center flex-shrink-0">
                        <p class="text-[10px] font-medium text-slate-500 italic mb-3">
                            Tunjukkan layar ini kepada Guru Mata Pelajaran<br>atau difoto/screenshot sebagai bukti masuk.
                        </p>
                        <button @click="viewingClassTicket = null"
                            class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl transition-colors text-sm shadow-md">
                            Tutup Tiket
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes bounce-in {
    0% {
        transform: scale(0.9);
        opacity: 0;
    }

    50% {
        transform: scale(1.02);
        opacity: 1;
    }

    100% {
        transform: scale(1);
    }
}

.animate-bounce-in {
    animation: bounce-in 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}
</style>
