<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

defineProps({
    products: Array,
});
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

                        <PrimaryButton type="button">
                            <slot v-if="products.length > 0">
                                Actualizar
                            </slot>

                            <slot v-else>
                                Despachos
                            </slot>
                        </PrimaryButton>

                    </div>

                    <div v-if="products.length > 0">

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