<template>
    <div class="min-h-screen bg-gradient-to-br from-rose-50 to-pink-100 flex items-center justify-center px-4">
        <Head title="Cambiar contraseña" />
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <div class="text-5xl mb-3">🔒</div>
                <h1 class="text-2xl font-bold text-gray-800">Cambiar contraseña</h1>
                <p class="text-gray-500 text-sm mt-1">Debes establecer una nueva contraseña para continuar</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña <span class="text-red-500">*</span></label>
                        <input type="password" v-model="form.password" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent"
                            placeholder="Mínimo 8 caracteres, mayúsculas y números"
                        />
                        <p v-if="form.errors.password" class="text-xs text-red-600 mt-1">{{ form.errors.password }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña <span class="text-red-500">*</span></label>
                        <input type="password" v-model="form.password_confirmation" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent"
                        />
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-xs text-amber-800">
                        La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.
                    </div>

                    <button type="submit" :disabled="form.processing"
                        class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-medium rounded-lg text-sm transition-colors disabled:opacity-60">
                        {{ form.processing ? 'Guardando...' : 'Establecer contraseña' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ password: '', password_confirmation: '' });

function submit() {
    form.post(route('password.change.store'), { onFinish: () => form.reset() });
}
</script>
