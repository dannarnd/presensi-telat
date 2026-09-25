<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false);

const form = useForm({
    login_id: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Masuk" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-400">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label for="login_id" class="block text-sm font-medium text-slate-300">Username / NIP / Nama Lengkap</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor" aria-hidden="true">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                    </div>
                    <input id="login_id" type="text" v-model="form.login_id" required autofocus autocomplete="username"
                        class="block w-full pl-10 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                        placeholder="Masukkan Username, NIP, atau Nama Lengkap" />
                </div>
                <InputError class="mt-2" :message="form.errors.login_id" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300">Kata Sandi</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input id="password" :type="showPassword ? 'text' : 'password'" v-model="form.password" required
                        autocomplete="current-password"
                        class="block w-full pl-10 pr-10 bg-slate-800/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-colors duration-200"
                        placeholder="••••••••" />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <button type="button" @click="showPassword = !showPassword"
                            class="text-slate-400 hover:text-slate-300 focus:outline-none">
                            <svg v-if="!showPassword" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg v-else class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" v-model="form.remember"
                        class="h-4 w-4 bg-slate-800 border-slate-600 rounded text-blue-600 focus:ring-blue-500 transition-colors" />
                    <label for="remember_me" class="ml-2 block text-sm text-slate-300">
                        Ingat saya
                    </label>
                </div>

            </div>

            <div>
                <button type="submit" :disabled="form.processing"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-slate-900 transform transition-all hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed">
                    Masuk
                </button>
            </div>

            <div class="mt-6 text-center border-t border-slate-700/50 pt-4">
                <p class="text-sm text-slate-400">
                    Belum punya akun? <br class="sm:hidden">
                    <span class="font-medium text-slate-300">Hubungi Admin Sekolah</span>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
