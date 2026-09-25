<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    students: Array,
    classes: Array,
    filters: Object,
});

const page = usePage();
const showStudentModal = ref(false);
const selectedClassId = ref(props.filters.class_id || '');

const studentForm = useForm({
    nisn: '',
    name: '',
    school_class_id: '',
    no_wa_ortu: '',
});

const submitStudent = () => {
    studentForm.post(route('admin.students.store'), {
        onSuccess: () => {
            showStudentModal.value = false;
            studentForm.reset();
        }
    });
};

const showEditModal = ref(false);
const editStudentForm = useForm({
    nisn: '',
    name: '',
    school_class_id: '',
    no_wa_ortu: '',
});
const studentToEdit = ref(null);

const openEditModal = (student) => {
    studentToEdit.value = student;
    editStudentForm.nisn = student.nisn;
    editStudentForm.name = student.name;
    editStudentForm.school_class_id = student.school_class_id;
    editStudentForm.no_wa_ortu = student.no_wa_ortu || '';
    showEditModal.value = true;
};

const submitEditStudent = () => {
    editStudentForm.put(route('admin.students.update', studentToEdit.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editStudentForm.reset();
        }
    });
};

const showDeleteModal = ref(false);
const studentToDelete = ref(null);
const deleteForm = useForm({});

const openDeleteModal = (student) => {
    studentToDelete.value = student;
    showDeleteModal.value = true;
};

const submitDeleteStudent = () => {
    deleteForm.delete(route('admin.students.destroy', studentToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
};

watch(selectedClassId, (newVal) => {
    router.get(route('admin.students.index'), { class_id: newVal }, {
        preserveState: true,
        replace: true
    });
});

// Bulk Actions
const selectedStudents = ref([]);
const selectAll = computed({
    get() {
        return props.students.length > 0 && selectedStudents.value.length === props.students.length;
    },
    set(val) {
        if (val) {
            selectedStudents.value = props.students.map(s => s.id);
        } else {
            selectedStudents.value = [];
        }
    }
});

const bulkAction = ref('');
const bulkTargetClassId = ref('');
const bulkForm = useForm({
    student_ids: [],
    action: '',
    target_class_id: '',
});

const submitBulkAction = () => {
    if (selectedStudents.value.length === 0) return alert('Pilih minimal satu siswa!');
    if (!bulkAction.value) return alert('Pilih aksi yang ingin dilakukan!');
    if (bulkAction.value === 'pindah_kelas' && !bulkTargetClassId.value) return alert('Pilih kelas tujuan!');

    bulkForm.student_ids = selectedStudents.value;
    bulkForm.action = bulkAction.value;
    bulkForm.target_class_id = bulkTargetClassId.value;

    bulkForm.post(route('admin.students.bulk-action'), {
        onSuccess: () => {
            selectedStudents.value = [];
            bulkAction.value = '';
            bulkTargetClassId.value = '';
        }
    });
};

// Import Excel
const showImportModal = ref(false);
const importForm = useForm({
    file: null,
});

const handleFileUpload = (e) => {
    importForm.file = e.target.files[0];
};

const submitImport = () => {
    importForm.post(route('admin.students.import'), {
        onSuccess: () => {
            showImportModal.value = false;
            importForm.reset();
        }
    });
};
const searchQuery = ref('');

const filteredStudents = computed(() => {
    let result = props.students;
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(student => 
            student.name.toLowerCase().includes(q) || 
            student.nisn.toLowerCase().includes(q)
        );
    }
    return result;
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">Data Siswa</h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div v-if="$page.props.flash.success" class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md shadow-sm mb-6 flex items-center">
                <svg class="h-6 w-6 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-green-700 font-medium">{{ $page.props.flash.success }}</span>
            </div>

            <div v-if="$page.props.flash.import_anomalies && $page.props.flash.import_anomalies.length > 0" class="bg-orange-50 border border-orange-200 rounded-xl shadow-sm mb-6 overflow-hidden animate-fade-in">
                <div class="bg-orange-500 px-4 py-3 flex items-center">
                    <svg class="h-5 w-5 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h3 class="text-white font-bold text-sm">Beberapa data tidak dapat diimpor karena anomali ({{ $page.props.flash.import_anomalies.length }} data)</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <table class="min-w-full divide-y divide-orange-100">
                        <thead class="bg-orange-100/50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-orange-800">Nama Siswa</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-orange-800">Kelas</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-orange-800">Alasan Gagal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-orange-100 bg-white">
                            <tr v-for="(anomaly, index) in $page.props.flash.import_anomalies" :key="index" class="hover:bg-orange-50/50">
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-bold text-slate-800">{{ anomaly.nama }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-slate-600">{{ anomaly.kelas }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-red-600">{{ anomaly.alasan }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="bg-orange-50 px-4 py-2 border-t border-orange-100 text-xs text-orange-700 italic">
                    * Data di atas dilewati (tidak tersimpan di database). Silakan perbaiki file Excel Anda dan upload ulang khusus untuk siswa-siswa ini.
                </div>
            </div>

            <!-- Filter and Actions -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-4 rounded-2xl shadow-sm border border-slate-100 gap-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-3 w-full md:w-auto">
                    <!-- Bulk Actions -->
                    <div v-if="selectedStudents.length > 0" class="flex items-center space-x-2 bg-blue-50 p-1.5 rounded-lg border border-blue-100">
                        <span class="text-xs font-bold text-blue-700 px-2">{{ selectedStudents.length }} terpilih</span>
                        <select v-model="bulkAction" class="text-sm rounded border-blue-200 text-blue-700 focus:ring-blue-500 py-1.5">
                            <option value="">-- Pilih Aksi --</option>
                            <option value="pindah_kelas">Pindah Kelas</option>
                            <option value="jadikan_alumni">Jadikan Alumni</option>
                        </select>
                        <select v-if="bulkAction === 'pindah_kelas'" v-model="bulkTargetClassId" class="text-sm rounded border-blue-200 text-blue-700 focus:ring-blue-500 py-1.5">
                            <option value="">-- Tujuan --</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                        </select>
                        <button @click="submitBulkAction" :disabled="bulkForm.processing" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-sm font-bold shadow disabled:opacity-50">Terapkan</button>
                    </div>

                    <!-- Filter -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-2 w-full" v-if="selectedStudents.length === 0">
                        <div class="relative w-full sm:w-64">
                            <input 
                                type="text" 
                                v-model="searchQuery" 
                                placeholder="Cari nama atau NISN..." 
                                class="w-full rounded-lg border-slate-300 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm py-2 pl-9 transition-colors"
                            >
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <select v-model="selectedClassId" class="w-full sm:w-40 rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 font-medium text-slate-700 text-sm">
                            <option value="">Semua Kelas</option>
                            <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <button @click="showImportModal = true" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md shadow-green-200 transition-all active:scale-95 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Import Excel
                    </button>
                    <button @click="showStudentModal = true" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md shadow-blue-200 transition-all active:scale-95 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Siswa Baru
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white overflow-hidden shadow-lg shadow-slate-200/50 rounded-2xl border border-slate-100 flex flex-col min-h-[500px]">
                <div class="overflow-auto flex-1 p-0">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-left"><input type="checkbox" v-model="selectAll" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"></th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">NISN</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Lengkap</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Kelas</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr v-for="student in filteredStudents" :key="student.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap"><input type="checkbox" :value="student.id" v-model="selectedStudents" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">{{ student.nisn }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">{{ student.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">{{ student.school_class?.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="student.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800'" class="px-2 py-1 rounded text-xs font-bold uppercase">{{ student.status }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-3">
                                    <button @click="openEditModal(student)" class="text-blue-500 hover:text-blue-700 font-bold transition-colors">Edit</button>
                                    <button @click="openDeleteModal(student)" class="text-red-500 hover:text-red-700 font-bold transition-colors">Hapus</button>
                                </td>
                            </tr>
                            <tr v-if="filteredStudents.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    <div class="inline-flex justify-center items-center w-12 h-12 rounded-full bg-slate-100 mb-3">
                                        <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    </div>
                                    <p>{{ searchQuery ? 'Tidak ada siswa yang cocok dengan pencarian Anda.' : 'Tidak ada data siswa ditemukan.' }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showStudentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-slate-800 mb-4">Tambah Siswa Baru</h3>
                    <form @submit.prevent="submitStudent" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">NISN</label>
                            <input type="text" v-model="studentForm.nisn" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                            <p v-if="studentForm.errors.nisn" class="mt-1 text-sm text-red-600">{{ studentForm.errors.nisn }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" v-model="studentForm.name" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                            <select v-model="studentForm.school_class_id" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">-- Pilih Kelas --</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">No. WA Orang Tua</label>
                            <input type="text" v-model="studentForm.no_wa_ortu" placeholder="0812..." class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="pt-4 flex justify-end space-x-3">
                            <button type="button" @click="showStudentModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                            <button type="submit" :disabled="studentForm.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 disabled:opacity-50">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-slate-800 mb-4">Edit Siswa</h3>
                    <form @submit.prevent="submitEditStudent" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">NISN</label>
                            <input type="text" v-model="editStudentForm.nisn" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                            <p v-if="editStudentForm.errors.nisn" class="mt-1 text-sm text-red-600">{{ editStudentForm.errors.nisn }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" v-model="editStudentForm.name" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                            <select v-model="editStudentForm.school_class_id" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">-- Pilih Kelas --</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">No. WA Orang Tua</label>
                            <input type="text" v-model="editStudentForm.no_wa_ortu" placeholder="0812..." class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="pt-4 flex justify-end space-x-3">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                            <button type="submit" :disabled="editStudentForm.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 disabled:opacity-50">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl p-6 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Hapus Siswa?</h3>
                    <p class="text-sm text-slate-500 mb-6">Anda yakin ingin menghapus <strong>{{ studentToDelete?.name }}</strong>?</p>
                    
                    <div class="flex justify-center space-x-3">
                        <button @click="showDeleteModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                        <button @click="submitDeleteStudent" :disabled="deleteForm.processing" class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-red-700 disabled:opacity-50 transition-colors">Ya, Hapus</button>
                    </div>
                </div>
            </div>
            <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-slate-800 mb-4">Import Data Siswa (Excel/CSV)</h3>
                    <div class="bg-blue-50 text-blue-800 p-3 rounded-lg text-sm mb-4">
                        <p class="font-bold">Format Kolom Wajib:</p>
                        <p>Baris pertama harus berisi judul kolom: <strong>NISN, Nama, Kelas, No WA</strong>.</p>
                        <p class="mt-1 text-xs text-blue-600">* Nama kelas disesuaikan teksnya, misal: X TKJ 1.</p>
                        <div class="mt-3">
                            <a href="/template_import_siswa.xlsx" download class="inline-flex items-center px-3 py-1.5 bg-white border border-blue-200 rounded-md text-xs font-bold text-blue-700 hover:bg-blue-100 transition-colors shadow-sm">
                                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                Download Template Excel
                            </a>
                        </div>
                    </div>
                    <form @submit.prevent="submitImport" class="space-y-4">
                        <div>
                            <input type="file" @change="handleFileUpload" accept=".xlsx, .csv" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        </div>
                        <div class="pt-4 flex justify-end space-x-3">
                            <button type="button" @click="showImportModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                            <button type="submit" :disabled="importForm.processing || !importForm.file" class="bg-green-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-green-700 disabled:opacity-50">Upload & Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
