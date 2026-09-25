<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    students: Array,
});

const searchQuery = ref('');
const filteredStudents = computed(() => {
    if (!searchQuery.value) return props.students;
    const q = searchQuery.value.toLowerCase();
    return props.students.filter(student =>
        student.name.toLowerCase().includes(q) ||
        student.nisn.toLowerCase().includes(q)
    );
});

// Fungsi ini meng-generate pesan WA (ini adalah file yang Anda cari)
const generateWaLink = (student) => {
    if (!student.no_wa_ortu) return '#';

    let phone = student.no_wa_ortu;
    if (phone.startsWith('0')) {
        phone = '62' + phone.substring(1);
    }

    // PESAN WA DI BAWAH INI ---
    const message = `Selamat pagi Bapak/Ibu, kami dari pihak sekolah menginformasikan bahwa ananda ${student.name} hingga saat ini telah tercatat datang terlambat sebanyak ${student.delay_logs_count} kali. Mohon kerjasamanya untuk membimbing ananda agar lebih disiplin dan tidak terlambat lagi di kemudian hari. Terima kasih.`;
    // ----------------------------------

    return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
};

const getWarningBadge = (count) => {
    if (count >= 5) {
        return { class: 'bg-red-100 text-red-800 border-red-200', text: 'Sangat Sering (Panggil Ortu)' };
    } else if (count >= 3) {
        return { class: 'bg-orange-100 text-orange-800 border-orange-200', text: 'Sering (Peringatan)' };
    } else if (count > 0) {
        return { class: 'bg-yellow-100 text-yellow-800 border-yellow-200', text: 'Pernah Telat' };
    }
    return { class: 'bg-green-100 text-green-800 border-green-200', text: 'Aman' };
};

// Detail Modal
const showDetailModal = ref(false);
const selectedStudent = ref(null);

const openDetail = (student) => {
    selectedStudent.value = student;
    showDetailModal.value = true;
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">Siswa Bimbingan</h2>
        </template>

        <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Informative Banner -->
            <div
                class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 md:p-8 text-white shadow-lg flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex-1">
                    <h3 class="text-2xl font-black mb-2">Pantau Kedisiplinan Anak Didik Anda</h3>
                    <p class="text-blue-100 font-medium leading-relaxed">
                        Halaman ini dikhususkan untuk Anda sebagai Wali Kelas. Daftar di bawah hanya berisi siswa-siswi
                        dari
                        kelas perwalian Anda yang <strong>pernah terlambat</strong>. Klik "Rincian" untuk melihat
                        tanggal mereka
                        telat, dan klik tombol <span
                            class="bg-green-500 text-white px-2 py-0.5 rounded text-xs font-bold mx-1">Hubungi
                            Ortu</span> untuk
                        mengirim pesan WhatsApp.
                    </p>
                </div>
                <div
                    class="hidden md:flex h-16 w-16 bg-white/20 rounded-full items-center justify-center backdrop-blur-sm shrink-0">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>

            <!-- Stats Overview -->
            <div
                class="bg-white overflow-hidden shadow-lg shadow-slate-200/50 rounded-2xl border border-slate-100 flex flex-col min-h-[500px]">
                <div
                    class="p-6 border-b border-slate-100 flex flex-col md:flex-row justify-between md:items-center bg-white gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Daftar Anak Bimbingan (Yang Pernah Telat)</h3>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="relative">
                            <input type="text" v-model="searchQuery" placeholder="Cari siswa atau NISN..."
                                class="w-full sm:w-64 text-sm border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm py-2 pl-9 pr-3 transition-colors">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <div
                            class="bg-blue-100 text-blue-700 font-bold px-3 py-1.5 rounded-lg text-sm whitespace-nowrap">
                            {{ filteredStudents.length }} Siswa
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto p-0 flex-1">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50 sticky top-0 z-10">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    NISN
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Siswa
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Total Telat</th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100">
                            <tr v-for="student in filteredStudents" :key="student.id"
                                class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono">{{ student.nisn
                                }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-base font-bold text-slate-900">{{ student.name }}</div>
                                    <div class="text-sm font-medium text-slate-500 mt-1">{{ student.school_class?.name
                                    }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="text-2xl font-black text-slate-800 mb-1">{{ student.delay_logs_count }}
                                    </div>
                                    <span
                                        :class="['px-2 py-0.5 inline-flex text-xs font-bold rounded-full border', getWarningBadge(student.delay_logs_count).class]">
                                        {{ getWarningBadge(student.delay_logs_count).text }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                                    <button @click="openDetail(student)"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-bold rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors">
                                        Rincian
                                    </button>
                                    <a v-if="student.no_wa_ortu" :href="generateWaLink(student)" target="_blank"
                                        class="inline-flex items-center px-4 py-1.5 border border-transparent text-sm font-bold rounded-lg text-white bg-[#25D366] hover:bg-[#1DA851] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#25D366] transition-colors shadow-md shadow-[#25D366]/30 active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="currentColor"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z" />
                                        </svg>
                                        Hubungi Ortu
                                    </a>
                                    <span v-if="!student.no_wa_ortu"
                                        class="text-slate-400 italic font-medium text-xs px-2 py-1">No WA Kosong</span>
                                </td>
                            </tr>
                            <tr v-if="filteredStudents.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div
                                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-slate-500 font-medium">
                                        {{ searchQuery ? 'Pencarian tidak ditemukan.' : 'Luar biasa! Seluruh anak didik Anda tidak pernah terlambat.' }}
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detail Modal -->
        <Teleport to="body">
            <div v-if="showDetailModal"
                class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-fade-in">
                <div
                    class="bg-white rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                    <div
                        class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center sticky top-0">
                        <div class="flex items-center space-x-4">
                            <img src="/images/logo.png" alt="Logo SMK" class="h-12 w-auto object-contain">
                            <div>
                                <h3 class="text-xl font-bold text-slate-800">{{ selectedStudent?.name }}</h3>
                                <p class="text-sm text-slate-500 font-medium">Riwayat Keterlambatan</p>
                            </div>
                        </div>
                        <button @click="showDetailModal = false"
                            class="text-slate-400 hover:text-slate-600 bg-white p-2 rounded-full shadow-sm hover:shadow-md transition-all">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 overflow-y-auto flex-1 bg-slate-50/50">
                        <div
                            class="space-y-4 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
                            <div v-for="log in selectedStudent.delay_logs" :key="log.id"
                                class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">

                                <div
                                    class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white bg-blue-100 text-blue-600 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                <div
                                    class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white p-4 rounded-xl shadow-sm border border-slate-100">
                                    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-1">
                                        <div class="font-bold text-slate-800">{{ new
                                            Date(log.delay_time).toLocaleDateString('id-ID', {
                                                weekday: 'long', day:
                                                    'numeric',
                                                month: 'long', year: 'numeric'
                                            }) }}</div>
                                        <div class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ new
                                            Date(log.delay_time).toLocaleTimeString('id-ID', {
                                                hour: '2-digit', minute:
                                                    '2-digit'
                                            }) }}</div>
                                    </div>
                                    <div class="text-slate-600 text-sm">Alasan: <strong>{{ log.reason }}</strong></div>
                                    <div class="text-slate-500 text-xs mt-1 font-medium italic" v-if="log.reporter_name">Petugas: {{ log.reporter_name }}</div>
                                </div>
                            </div>
                        </div>
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
