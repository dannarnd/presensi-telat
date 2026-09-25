<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    classes: Array,
    waliKelasList: Array,
});

const page = usePage();
const showClassModal = ref(false);

const classForm = useForm({
    name: '',
    wali_kelas_id: '',
});

const submitClass = () => {
    classForm.post(route('admin.classes.store'), {
        onSuccess: () => {
            showClassModal.value = false;
            classForm.reset();
        }
    });
};

const showEditModal = ref(false);
const editClassForm = useForm({
    name: '',
    wali_kelas_id: '',
});
const classToEdit = ref(null);

const openEditModal = (cls) => {
    classToEdit.value = cls;
    editClassForm.name = cls.name;
    editClassForm.wali_kelas_id = cls.wali_kelas_id || '';
    showEditModal.value = true;
};

const submitEditClass = () => {
    editClassForm.put(route('admin.classes.update', classToEdit.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editClassForm.reset();
        }
    });
};

const showDeleteModal = ref(false);
const classToDelete = ref(null);
const deleteForm = useForm({});

const openDeleteModal = (cls) => {
    if (cls.students_count > 0) return; // Protected by UI logic
    classToDelete.value = cls;
    showDeleteModal.value = true;
};

const submitDeleteClass = () => {
    deleteForm.delete(route('admin.classes.destroy', classToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">Data Kelas</h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div v-if="$page.props.flash.success" class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md shadow-sm mb-6 flex items-center">
                <svg class="h-6 w-6 text-green-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-green-700 font-medium">{{ $page.props.flash.success }}</span>
            </div>

            <div v-if="$page.props.flash.error" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md shadow-sm mb-6 flex items-center">
                <svg class="h-6 w-6 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-red-700 font-medium">{{ $page.props.flash.error }}</span>
            </div>

            <div class="bg-white overflow-hidden shadow-lg shadow-slate-200/50 rounded-2xl border border-slate-100 flex flex-col h-[600px]">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-white">
                    <div>
                        <h3 class="font-bold text-xl text-slate-800">Daftar Kelas</h3>
                        <p class="text-sm text-slate-500 mt-1">Kelola data kelas dan penempatan wali kelas</p>
                    </div>
                    <button @click="showClassModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md shadow-blue-200 transition-all active:scale-95 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Kelas Baru
                    </button>
                </div>
                <div class="overflow-auto flex-1 p-0">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Kelas</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Wali Kelas</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Total Siswa</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr v-for="cls in classes" :key="cls.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">{{ cls.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ cls.wali_kelas?.name || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium text-slate-600 bg-slate-50/50">{{ cls.students_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-3">
                                    <button @click="openEditModal(cls)" class="text-blue-500 hover:text-blue-700 font-bold transition-colors">Edit</button>
                                    <button 
                                        @click="openDeleteModal(cls)" 
                                        :class="['font-bold transition-colors', cls.students_count > 0 ? 'text-slate-300 cursor-not-allowed' : 'text-red-500 hover:text-red-700']"
                                        :title="cls.students_count > 0 ? 'Pindahkan dulu siswa di kelas ini sebelum menghapus' : 'Hapus Kelas'"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="classes.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada data kelas</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showClassModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-slate-800 mb-4">Tambah Kelas Baru</h3>
                    <form @submit.prevent="submitClass" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kelas</label>
                            <input type="text" v-model="classForm.name" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                            <p v-if="classForm.errors.name" class="mt-1 text-sm text-red-600">{{ classForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Wali Kelas (Opsional)</label>
                            <select v-model="classForm.wali_kelas_id" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Pilih Wali Kelas --</option>
                                <option v-for="wk in waliKelasList" :key="wk.id" :value="wk.id">{{ wk.name }}</option>
                            </select>
                        </div>
                        <div class="pt-4 flex justify-end space-x-3">
                            <button type="button" @click="showClassModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                            <button type="submit" :disabled="classForm.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 disabled:opacity-50">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-slate-800 mb-4">Edit Kelas</h3>
                    <form @submit.prevent="submitEditClass" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kelas</label>
                            <input type="text" v-model="editClassForm.name" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                            <p v-if="editClassForm.errors.name" class="mt-1 text-sm text-red-600">{{ editClassForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Wali Kelas (Opsional)</label>
                            <select v-model="editClassForm.wali_kelas_id" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Pilih Wali Kelas --</option>
                                <option v-for="wk in waliKelasList" :key="wk.id" :value="wk.id">{{ wk.name }}</option>
                            </select>
                        </div>
                        <div class="pt-4 flex justify-end space-x-3">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                            <button type="submit" :disabled="editClassForm.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 disabled:opacity-50">Simpan Perubahan</button>
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
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Hapus Kelas?</h3>
                    <p class="text-sm text-slate-500 mb-6">Anda yakin ingin menghapus kelas <strong>{{ classToDelete?.name }}</strong>?</p>
                    
                    <div class="flex justify-center space-x-3">
                        <button @click="showDeleteModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                        <button @click="submitDeleteClass" :disabled="deleteForm.processing" class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-red-700 disabled:opacity-50 transition-colors">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
