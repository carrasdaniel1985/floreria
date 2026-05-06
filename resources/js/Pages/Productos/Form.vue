<template>
    <Head :title="producto ? 'Editar producto' : 'Nuevo producto'" />
    <AuthenticatedLayout>
        <PageHeader :title="producto ? 'Editar producto' : 'Nuevo producto'">
            <template #actions>
                <Link :href="route('productos.index')" class="btn-outline">← Volver</Link>
            </template>
        </PageHeader>

        <div class="max-w-3xl">
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Info básica -->
                <div class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
                    <h3 class="font-semibold text-gray-700">Información básica</h3>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="label">Nombre <span class="text-red-500">*</span></label>
                            <input v-model="form.nombre" type="text" required class="input w-full" />
                            <p v-if="form.errors.nombre" class="error">{{ form.errors.nombre }}</p>
                        </div>
                        <div>
                            <label class="label">Categoría</label>
                            <select v-model="form.categoria_id" class="input w-full">
                                <option value="">Sin categoría</option>
                                <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">Tipo <span class="text-red-500">*</span></label>
                            <select v-model="form.tipo" required class="input w-full">
                                <option value="simple">Producto simple</option>
                                <option value="servicio">Servicio</option>
                            </select>
                        </div>
                        <div>
                            <label class="label">SKU</label>
                            <input v-model="form.sku" type="text" class="input w-full" placeholder="Código único" />
                            <p v-if="form.errors.sku" class="error">{{ form.errors.sku }}</p>
                        </div>
                        <div>
                            <label class="label">Tamaño</label>
                            <input v-model="form.tamanio" type="text" class="input w-full" placeholder="Ej: Pequeño, Mediano, Grande" />
                        </div>
                        <div>
                            <label class="label">Tonos / Colores</label>
                            <input v-model="form.tonos" type="text" class="input w-full" placeholder="Ej: Rosado, Blanco, Rojo" />
                        </div>
                        <div>
                            <label class="label">Temporada</label>
                            <input v-model="form.temporada" type="text" class="input w-full" placeholder="Ej: Primavera, Todo el año" />
                        </div>
                        <div>
                            <label class="label">Vida útil (días)</label>
                            <input v-model="form.vida_util_dias" type="number" min="1" class="input w-full" placeholder="Días de vida útil" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label">Descripción</label>
                            <textarea v-model="form.descripcion" rows="3" class="input w-full resize-none"></textarea>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 cursor-pointer" v-if="form.tipo !== 'servicio'">
                            <input type="checkbox" v-model="form.maneja_stock" class="rounded border-gray-300 text-rose-600 focus:ring-rose-400" />
                            <span class="text-sm text-gray-700">Maneja stock</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.es_destacado" class="rounded border-gray-300 text-rose-600 focus:ring-rose-400" />
                            <span class="text-sm text-gray-700">Producto destacado</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.activo" class="rounded border-gray-300 text-rose-600 focus:ring-rose-400" />
                            <span class="text-sm text-gray-700">Activo</span>
                        </label>
                    </div>
                </div>

                <!-- Precio de venta -->
                <div v-if="!producto" class="bg-white rounded-xl border border-gray-100 p-6 space-y-4">
                    <h3 class="font-semibold text-gray-700">Precio de venta inicial</h3>
                    <div>
                        <label class="label">Precio (CLP, incluye IVA)</label>
                        <input v-model="form.precio_venta" type="number" min="0" class="input w-48" placeholder="0" />
                        <p class="text-xs text-gray-400 mt-1">Podrás modificar el precio desde la ficha del producto.</p>
                    </div>
                </div>

                <!-- Fotos -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-700 mb-3">Fotos</h3>
                    <div v-if="producto?.fotos?.length" class="flex flex-wrap gap-2 mb-3">
                        <img v-for="f in producto.fotos" :key="f.id" :src="`/storage/${f.path}`" class="w-16 h-16 object-cover rounded-lg border" />
                    </div>
                    <input type="file" @change="e => form.fotos = Array.from(e.target.files)" accept="image/*" multiple class="text-sm text-gray-600" />
                    <p class="text-xs text-gray-400 mt-1">Puedes subir varias fotos. La primera será la foto principal.</p>
                </div>

                <!-- Stock mínimo -->
                <div v-if="form.maneja_stock && form.tipo !== 'servicio'" class="bg-white rounded-xl border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-700 mb-3">Stock</h3>
                    <div>
                        <label class="label">Stock mínimo (alerta)</label>
                        <input v-model="form.stock_minimo" type="number" min="0" class="input w-32" />
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-60">
                        {{ form.processing ? 'Guardando...' : (producto ? 'Actualizar' : 'Crear producto') }}
                    </button>
                    <Link :href="route('productos.index')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({ producto: Object, categorias: Array });

const form = useForm({
    nombre:        props.producto?.nombre ?? '',
    descripcion:   props.producto?.descripcion ?? '',
    categoria_id:  props.producto?.categoria_id ?? '',
    tipo:          props.producto?.tipo ?? 'simple',
    tamanio:       props.producto?.tamanio ?? '',
    tonos:         props.producto?.tonos ?? '',
    sku:           props.producto?.sku ?? '',
    temporada:     props.producto?.temporada ?? '',
    vida_util_dias: props.producto?.vida_util_dias ?? '',
    maneja_stock:  props.producto?.maneja_stock ?? true,
    es_destacado:  props.producto?.es_destacado ?? false,
    activo:        props.producto?.activo ?? true,
    stock_minimo:  props.producto?.stock_minimo ?? 0,
    precio_venta:  '',
    fotos:         [],
});

function submit() {
    const opts = { forceFormData: true };
    if (props.producto) {
        form.post(route('productos.update', props.producto.id), { ...opts, _method: 'put' });
    } else {
        form.post(route('productos.store'), opts);
    }
}
</script>
<style scoped>
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-rose-400; }
.error { @apply text-xs text-red-600 mt-1; }
.btn-outline { @apply px-3 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors; }
</style>
