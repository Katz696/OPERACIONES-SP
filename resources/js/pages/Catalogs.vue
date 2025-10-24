<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import projects from '../components/catalogs.views/projects.vue'
import users from '../components/catalogs.views/users.vue'
import customers from '@/components/catalogs.views/customers.vue';
import { useProjectStore, useUserStore, useCustomersStore } from '@/composables/useCatalogStore'

import { DocumentOutline, PersonAddOutline } from '@vicons/ionicons5'

const projectStore = useProjectStore()
const userStore = useUserStore()
const customerStore = useCustomersStore();
const tabs = ['Proyectos', 'Usuarios', 'Clientes']
const activeTab = ref('Proyectos')

const init = async () => {
    const prors = await axios.get(route('catalogs.projects'))
    projectStore.setData(prors.data)
    const usrs = await axios.get(route('catalogs.users'))
    userStore.setData(usrs.data)
    const cmrs = await axios.get(route('catalogs.customers'))
    customerStore.setData(cmrs.data)
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Catalogos',
        href: '/catalogos',
    },
];
onMounted(init)
//permisos
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const auth = page.props.auth.user
</script>

<template>

    <Head title="Catalogos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="p-4">
                <div class="flex space-x-4 border-b mb-4">
                    <n-button-group size="small">
                        <n-button round @click="activeTab = 'Proyectos'" :color="activeTab == 'Proyectos' ? '#ABABAB' : ''">
                            <template #icon>
                                <n-icon>
                                    <DocumentOutline />
                                </n-icon>
                            </template>
                            Proyectos
                        </n-button>
                        <n-button v-if="auth?.permissions?.includes('users.view')" @click="activeTab = 'Usuarios'" :color="activeTab == 'Usuarios' ? '#ABABAB' : ''">
                            <template #icon>
                                <n-icon>
                                    <PersonAddOutline />
                                </n-icon>
                            </template>
                            Usuarios
                        </n-button>
                        <n-button round @Click="activeTab = 'Clientes'" :color="activeTab == 'Clientes' ? '#ABABAB' : ''">
                            <template #icon>
                                <n-icon>
                                    <PersonAddOutline />
                                </n-icon>
                            </template>
                            Clientes
                        </n-button>
                    </n-button-group>
                </div>
                <div>
                    <template v-if="projectStore.edit">
                        <projects v-if="activeTab === 'Proyectos'" />
                    </template>
                    <template v-if="userStore.edit">
                        <users v-if="activeTab === 'Usuarios'" />
                    </template>
                    <template v-if="customerStore.edit">
                        <customers v-if="activeTab === 'Clientes'" />
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
