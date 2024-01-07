<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from '@/Components/InputLabel.vue';
import axios from 'axios';
import { onMounted, reactive, ref } from 'vue';
import swal from 'sweetalert';

const props = defineProps({
    dispatches: {
        Object,
        required: true,
    },
    id: String,
    apiToken: String,
});

const loading = ref(false)
const registros = ref(props.dispatches)
const startDate = ref()
const selectedRecords = ref(5)

async function saveDispatches(despachos) {
    return axios.post(
        "http://127.0.0.1:8000/despachos/new-dispatches",
        {
            data: {
                'dispatches': despachos,
            }
        }
    )
}

async function lastDispatches(apiToken, idBranch, dispatchId) {
    return axios.post(
        "http://mipuchito.test/api/last-dispatches",
        {
            headers: {
                Accept: "application/json",
            },
            data: {
                'apiToken': apiToken,
                'idBranch': idBranch,
                'dispatchId': dispatchId,
            }
        }
    )
}

async function maxIdDispatch() {
    return axios.post(
        "/despachos/max-id",
    )
}

function actualizarDespachos(apiToken, id) {
    this.loading = true;

    /* BUSCAR ULTIMO DESPACHO */
    maxIdDispatch().then((response) => {
        const lastDispatch = response.data.idDispatch;
        /* EVALUAR SI EXISTEN DESPACHOS EN EL PISO */
        if (lastDispatch > 0) {
            /* SOLICITAR ID MAX EN PROMETHEUS */
            lastDispatches(apiToken, id, lastDispatch).then((response) => {
                if (response.data.error) {
                    /* MOSTRAR ERROR */
                    console.log(response.data);
                    this.loading = false
                    swal({
                        title: 'Despachos',
                        text: 'El servidor genero un error. Por favor intenta mas tarde',
                        icon: 'error',
                    });
                } else {
                    if (response.data.despachos.length > 0) {
                        /* ACTUALIZAR DESPACHOS */
                        saveDispatches(response.data.despachos).then((response) => {
                            if (response.data.error) {
                                /* MOSTRAR ERROR */
                                console.log(response.data);
                                this.loading = false
                                swal({
                                    title: 'Despachos',
                                    text: 'Algo fallo mientras se guardaban los despachos. Por favor intenta mas tarde',
                                    icon: 'error',
                                });
                            } else {
                                /* NOTIFICAR SINCRONIZACION EXITOSA DE DESPACHO */
                                this.registros = response.data.despachos
                                this.loading = false
                                swal({
                                    title: 'Despachos',
                                    text: 'Despachos actualizados exitosamente.',
                                    icon: 'success',
                                });
                            }
                        }).catch((error) => {
                            /* MOSTRAR ERROR */
                            console.log(error);
                            this.loading = false
                            swal({
                                title: 'Despachos',
                                text: 'El sistema fallo. Por favor intenta mas tarde',
                                icon: 'error',
                            });
                        })
                    } else {
                        /* DESPACHOS ESTA ACTUALIZADO */
                        this.loading = false
                        swal({
                            title: 'Despachos',
                            text: 'No hay despachos nuevos.',
                            icon: 'success',
                        });
                    }
                }
            }).catch((error) => {
                /* MOSTRAR ERROR */
                console.log(error);
                this.loading = false
                swal({
                    title: 'Despachos',
                    text: 'Ha fallado la conexión con el servidor. Por favor intenta mas tarde',
                    icon: 'error',
                });
            })
        } else {
            /* PEDIR TODOS LOS DESPACHOS PARA EL PISO */
            lastDispatches(apiToken, id, 0).then((response) => {
                if (response.data.error) {
                    /* MOSTRAR ERROR */
                    console.log(response.data);
                    this.loading = false
                    swal({
                        title: 'Despachos',
                        text: 'Ha fallado la conexión con el servidor. Por favor intenta mas tarde',
                        icon: 'error',
                    });
                } else {
                    /* GUARDAR DESPACHO Y PRODUCTOS */
                    saveDispatches(response.data.despachos).then((response) => {
                        if (response.data.error) {
                            /* MOSTRAR ERROR */
                            console.log(response.data);
                            this.loading = false
                            swal({
                                title: 'Despachos',
                                text: 'Algo fallo mientras se guardaban los despachos. Por favor intenta mas tarde',
                                icon: 'error',
                            });
                        } else {
                            /* NOTIFICAR SINCRONIZACION EXITOSA DE DESPACHO */
                            this.registros = response.data.despachos
                            this.loading = false
                            swal({
                                title: 'Despachos',
                                text: 'Carga inicial exitosa.',
                                icon: 'success',
                            });
                        }
                    }).catch((error) => {
                        /* MOSTRAR ERROR */
                        console.log(error);
                        this.loading = false
                        swal({
                            title: 'Despachos',
                            text: 'El sistema fallo. Por favor intenta mas tarde',
                            icon: 'error',
                        });
                    })
                }
            }).catch((error) => {
                /* MOSTRAR ERROR */
                console.log(error);
                this.loading = false
                swal({
                    title: 'Despachos',
                    text: 'El sistema fallo. Por favor intenta mas tarde',
                    icon: 'error',
                });
            })
        }
    }).catch((error) => {
        console.log(error);
    })

}

function procesarDespacho(dispatchId, status) {
    this.loading = true
    /* CAMBIAR STATUS DEL DESPACHO */
    axios.post(
        "/despachos/procesar",
        {
            headers: {
                Accept: "application/json",
            },
            data: {
                'dispatchId': dispatchId,
                'status': status,
            }
        }
    ).then((response) => {
        if (response.data.error) {
            /* MOSTRAR ERROR */
            console.log(response);
            this.loading = false
            swal({
                title: 'Despachos',
                text: response.data.errorMessage,
                icon: 'error',
            });
        } else {
            this.loading = false
            this.selectedRecords = 5
            this.registros = response.data.dispatches;
            swal({
                title: 'Despachos',
                text: 'Despacho procesado exitosamente.',
                icon: 'success',
            });
        }
    }).catch((error) => {
        /* MOSTRAR ERROR */
        console.log(error);
        this.loading = false
        swal({
            title: 'Despachos',
            text: 'El sistema fallo. Por favor intenta mas tarde.',
            icon: 'error',
        })
    })
}

function fetchData(page = 1, startDate = null, records = selectedRecords) {
    axios.get(`/despachos/buscar?page=${page}&startDate=${startDate}&records=${records}`).then(response => {
        this.registros = response.data.registros;
    }).catch((error) => {
        console.log(error);
    });
}

onMounted(() => {
    fetchData
});

</script>

<template>
    <AppLayout title="Inventario">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Despachos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">

                    <div class="flex justify-between">

                        <span class="inline-flex items-center">
                            <InputLabel for="startDate" value="Fecha" />
                            <TextInput id="startDate" class="mx-3" type="date" v-model="startDate" />
                            <PrimaryButton class="mx-3" type="button" :disabled="loading"
                                @click="fetchData(1, startDate, selectedRecords)">
                                <slot v-if="loading">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-6 h-6 animate-spin">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                </slot>

                                <slot v-else>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>

                                </slot>
                            </PrimaryButton>
                        </span>

                        <PrimaryButton type="button" :disabled="loading" @click="actualizarDespachos(apiToken, id)">
                            <slot v-if="loading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-6 h-6 animate-spin">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </slot>

                            <slot v-else>
                                Actualizar
                            </slot>
                        </PrimaryButton>

                    </div>

                    <div class="mt-4" v-if="registros.data.length > 0">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                        #</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                        Fecha</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">
                                        Status</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                <tr class="hover:bg-gray-200" v-for="dispatch in registros.data" :key="dispatch.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        {{ dispatch.prometheus_id }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        {{ dispatch.created_at }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center" v-if="dispatch.status == 'espera'">
                                        <button :disabled="loading"
                                            class="disabled:opacity-70 disabled:cursor-not-allowed mx-2 cursor-pointer bg-green-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 inline-flex items-center px-4 py-2"
                                            @click="procesarDespacho(dispatch.prometheus_id, 1)">
                                            <span v-if="loading">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6 animate-spin">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>
                                            </span>

                                            <span v-else>
                                                Aceptar
                                            </span>
                                        </button>

                                        <button :disabled="loading"
                                            class="disabled:opacity-70 disabled:cursor-not-allowed mx-2 cursor-pointer bg-red-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 inline-flex items-center px-4 py-2"
                                            @click="procesarDespacho(dispatch.prometheus_id, 0)">
                                            <span v-if="loading">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-6 h-6 animate-spin">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>
                                            </span>

                                            <span v-else>
                                                Rechazar
                                            </span>
                                        </button>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center" v-else>
                                        {{ dispatch.status }}
                                    </td>

                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-4">
                            <nav class="flex items-center justify-between">
                                <div class="inline-flex items-center">
                                    <InputLabel for="records" value="Cantidad de Registros:" />
                                    <select @change="fetchData(registros.current_page, startDate, selectedRecords)"
                                        id="records" v-model="selectedRecords"
                                        class="border border-gray-300 bg-white text-gray-700 rounded-md ps-4 pe-8 py-2 mx-2">
                                        <option value="5">5</option>
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="30">30</option>
                                        <option value="50">50</option>
                                    </select>
                                    <p class="text-sm text-gray-700">
                                        Mostrando del {{ registros.from }} al {{ registros.to }} de un total de {{
                                            registros.total }} registros
                                    </p>
                                </div>
                                <div>
                                    <ul class="inline-flex space-x-2">
                                        <li v-if="registros.current_page > 1">
                                            <button
                                                @click="fetchData(registros.current_page - 1, startDate, selectedRecords)"
                                                class="px-3 py-1 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300">Anterior</button>
                                        </li>
                                        <li v-for="page in registros.last_page" :key="page">
                                            <button @click="fetchData(page, startDate, selectedRecords)"
                                                :class="{ 'bg-blue-500 text-white': page === registros.current_page, 'bg-gray-200 text-gray-700 hover:bg-gray-300': page !== registros.current_page }"
                                                class="px-3 py-1 rounded-md">{{ page }}</button>
                                        </li>
                                        <li v-if="registros.current_page < registros.last_page">
                                            <button
                                                @click="fetchData(registros.current_page + 1, startDate, selectedRecords)"
                                                class="px-3 py-1 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300">Siguiente</button>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                    </div>

                    <div v-else class="flex mt-10">
                        <p class="text-sm text-gray-700" v-if="startDate != null">
                            No existen despachos para esta fecha.
                        </p>
                        <p class="text-sm text-gray-700" v-else>
                            Haz click en el boton actualizar para revisar si hay nuevos despachos.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>