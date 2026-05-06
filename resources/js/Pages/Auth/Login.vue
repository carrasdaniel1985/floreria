<template>
    <div class="min-h-screen bg-gradient-to-br from-rose-50 to-pink-100 flex items-center justify-center px-4">
        <Head title="Iniciar sesión" />
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="text-5xl mb-3">🌸</div>
                <h1 class="text-2xl font-bold text-gray-800">Florería</h1>
                <p class="text-gray-500 text-sm mt-1">Sistema interno de gestión</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div v-if="status" class="mb-4 text-sm text-green-700 bg-green-50 rounded-lg px-4 py-3">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                        <input
                            id="email" type="email" v-model="form.email"
                            required autofocus autocomplete="username"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent"
                            placeholder="tu@correo.cl"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-600 mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input
                            id="password" type="password" v-model="form.password"
                            required autocomplete="current-password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="text-xs text-red-600 mt-1">{{ form.errors.password }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-medium rounded-lg text-sm transition-colors disabled:opacity-60"
                    >
                        {{ form.processing ? 'Ingresando...' : 'Ingresar' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';

defineProps({ status: String });

const form = useForm({ email: '', password: '' });

function submit() {
    form.post(route('login'), { onFinish: () => form.reset('password') });
}
</script>
