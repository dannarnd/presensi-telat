<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: Array,
});

const page = usePage();
const showUserModal = ref(false);

const userForm = useForm({
    name: '',
    username: '',
    nip: '',
    password: '',
    role: '',
});

const submitUser = () => {
    userForm.post(route('admin.users.store'), {
        onSuccess: () => {
            showUserModal.value = false;
            userForm.reset();
        }
    });
};

const showResetModal = ref(false);
const selectedUser = ref(null);

const resetForm = useForm({
    password: '',
});

const openResetModal = (user) => {
    selectedUser.value = user;
    resetForm.password = '';
    showResetModal.value = true;
};

const submitResetPassword = () => {
    resetForm.post(route('admin.users.reset-password', selectedUser.value.id), {
        onSuccess: () => {
            showResetModal.value = false;
            resetForm.reset();
        }
    });
};

const showEditModal = ref(false);
const editUserForm = useForm({
    name: '',
    username: '',
    nip: '',
    password: '',
    role: '',
});
const userToEdit = ref(null);

const openEditModal = (user) => {
    userToEdit.value = user;
    editUserForm.name = user.name;
    editUserForm.username = user.username;
    editUserForm.nip = user.nip;
    editUserForm.password = ''; // left blank unless changing
    editUserForm.role = user.role;
    showEditModal.value = true;
};

const submitEditUser = () => {
    editUserForm.put(route('admin.users.update', userToEdit.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editUserForm.reset();
        }
    });
};

const showDeleteModal = ref(false);
const userToDelete = ref(null);
const deleteForm = useForm({});

const openDeleteModal = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const submitDeleteUser = () => {
    deleteForm.delete(route('admin.users.destroy', userToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        }
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">Data Pengguna</h2>
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
                        <h3 class="font-bold text-xl text-slate-800">Daftar Pengguna (User)</h3>
                        <p class="text-sm text-slate-500 mt-1">Kelola data akses seperti Wali Kelas, Guru Piket, dll</p>
                    </div>
                    <button @click="showUserModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md shadow-blue-200 transition-all active:scale-95 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Pengguna Baru
                    </button>
                </div>
                <div class="overflow-auto flex-1 p-0">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Nama & Gelar</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">NIP</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr v-for="u in users" :key="u.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">{{ u.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ u.username || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ u.nip || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold capitalize bg-blue-100 text-blue-800 border border-blue-200">{{ u.role.replace('_', ' ') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-3">
                                    <button @click="openEditModal(u)" class="text-blue-500 hover:text-blue-700 font-bold transition-colors">Edit</button>
                                    <button @click="openResetModal(u)" class="text-orange-500 hover:text-orange-700 font-bold transition-colors">Reset Password</button>
                                    <button @click="openDeleteModal(u)" class="text-red-500 hover:text-red-700 font-bold transition-colors">Hapus</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-slate-800 mb-4">Tambah Pengguna Baru</h3>
                    <form @submit.prevent="submitUser" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" v-model="userForm.name" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                            <p v-if="userForm.errors.name" class="mt-1 text-sm text-red-600">{{ userForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Pengguna (Username)</label>
                            <input type="text" v-model="userForm.username" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Untuk mempermudah login" required>
                            <p v-if="userForm.errors.username" class="mt-1 text-sm text-red-600">{{ userForm.errors.username }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">NIP (Opsional)</label>
                            <input type="text" v-model="userForm.nip" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            <p v-if="userForm.errors.nip" class="mt-1 text-sm text-red-600">{{ userForm.errors.nip }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                            <input type="password" v-model="userForm.password" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                            <p v-if="userForm.errors.password" class="mt-1 text-sm text-red-600">{{ userForm.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Role / Peran</label>
                            <select v-model="userForm.role" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">-- Pilih Peran --</option>
                                <option value="admin">Admin</option>
                                <option value="guru_piket">Guru Piket</option>
                                <option value="wali_kelas">Wali Kelas</option>
                                <option value="kepala_sekolah">Kepala Sekolah</option>
                            </select>
                            <p v-if="userForm.errors.role" class="mt-1 text-sm text-red-600">{{ userForm.errors.role }}</p>
                        </div>
                        <div class="pt-4 flex justify-end space-x-3">
                            <button type="button" @click="showUserModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                            <button type="submit" :disabled="userForm.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 disabled:opacity-50">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div v-if="showResetModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Reset Password</h3>
                    <p class="text-sm text-slate-500 mb-4">Setel ulang password untuk pengguna <strong>{{ selectedUser?.name }}</strong>.</p>
                    <form @submit.prevent="submitResetPassword" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                            <input type="password" v-model="resetForm.password" class="w-full rounded-lg border-slate-300 focus:border-orange-500 focus:ring-orange-500" required placeholder="minimal 8 karakter">
                            <p v-if="resetForm.errors.password" class="mt-1 text-sm text-red-600">{{ resetForm.errors.password }}</p>
                        </div>
                        <div class="pt-4 flex justify-end space-x-3">
                            <button type="button" @click="showResetModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                            <button type="submit" :disabled="resetForm.processing" class="bg-orange-500 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-orange-600 disabled:opacity-50">Reset</button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-slate-800 mb-4">Edit Pengguna</h3>
                    <form @submit.prevent="submitEditUser" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" v-model="editUserForm.name" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                            <p v-if="editUserForm.errors.name" class="mt-1 text-sm text-red-600">{{ editUserForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Pengguna (Username)</label>
                            <input type="text" v-model="editUserForm.username" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Untuk mempermudah login" required>
                            <p v-if="editUserForm.errors.username" class="mt-1 text-sm text-red-600">{{ editUserForm.errors.username }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">NIP (Opsional)</label>
                            <input type="text" v-model="editUserForm.nip" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
                            <p v-if="editUserForm.errors.nip" class="mt-1 text-sm text-red-600">{{ editUserForm.errors.nip }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru (Opsional)</label>
                            <input type="password" v-model="editUserForm.password" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" placeholder="Kosongkan jika tidak ingin mengubah">
                            <p v-if="editUserForm.errors.password" class="mt-1 text-sm text-red-600">{{ editUserForm.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Role / Peran</label>
                            <select v-model="editUserForm.role" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">-- Pilih Peran --</option>
                                <option value="admin">Admin</option>
                                <option value="guru_piket">Guru Piket</option>
                                <option value="wali_kelas">Wali Kelas</option>
                                <option value="kepala_sekolah">Kepala Sekolah</option>
                                <option value="guru_bk">Guru BK</option>
                            </select>
                            <p v-if="editUserForm.errors.role" class="mt-1 text-sm text-red-600">{{ editUserForm.errors.role }}</p>
                        </div>
                        <div class="pt-4 flex justify-end space-x-3">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</button>
                            <button type="submit" :disabled="editUserForm.processing" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-blue-700 disabled:opacity-50">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl p-6 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Hapus Pengguna?</h3>
                    <p class="text-sm text-slate-500 mb-6">Anda yakin ingin menghapus pengguna <strong>{{ userToDelete?.name }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                    
                    <div class="flex justify-center space-x-3">
                        <button @click="showDeleteModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                        <button @click="submitDeleteUser" :disabled="deleteForm.processing" class="bg-red-600 text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-red-700 disabled:opacity-50 transition-colors">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
