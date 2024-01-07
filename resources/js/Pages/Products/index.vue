<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { reactive } from 'vue';

const props = defineProps({
    products: {
        Array,
        require: true,
    },
});

const productsTable = reactive(props.products);

</script>

<template>
    <AppLayout title="Inventario">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Inventario
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

                    <div class="flex justify-between">

                        <TextInput placeholder="Buscar..." />

                        <div v-if="products.length > 0">
                            <PrimaryButton type="button">
                                <slot>
                                    Actualizar
                                </slot>
                            </PrimaryButton>
                        </div>

                        <div v-else>
                            <PrimaryButton type="button">
                                <slot>
                                    <a href="/despachos">Ir a Despachos</a>
                                </slot>
                            </PrimaryButton>
                        </div>

                    </div>

                    <div class="mt-4" v-if="products.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                        Nombre</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                        Cantidad</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                        Precio</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                <tr class="hover:bg-gray-200" v-for="product in productsTable" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        {{ product.nombre }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        {{ product.cantidad }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        {{ product.precio_menor }}
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="flex mt-10">
                        <p>
                            Primero acepta algun despacho para cargar productos.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>