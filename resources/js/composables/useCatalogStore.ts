import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import * as types from '@/types/catalogs';

export const useProjectStore = defineStore('propjects', () => {

    const origin = ref<types.Project[] | null>(null)
    const edit = ref<types.Project[] | null>(null)

    function setData(data:types.Response<types.Project>){
        origin.value = data.data
        edit.value = JSON.parse(JSON.stringify(data.data))
    }

    const hasChanges = computed(() => {
        return JSON.stringify(origin.value) !== JSON.stringify(edit.value);
    });
    function discardChanges(){
        edit.value = origin.value ? JSON.parse(JSON.stringify(origin.value)) :  null;
    }
    function updateEdit(newData: types.Project[]){
        edit.value = newData;
    }

    return {
        origin,
        edit,
        setData,
        hasChanges,
        discardChanges,
        updateEdit
    }
});

export const useUserStore = defineStore('users', () => {

    const origin = ref<types.User[] | null>(null)
    const edit = ref<types.User[] | null>(null)

    function setData(data:types.Response<types.User>){
        origin.value = data.data
        edit.value = JSON.parse(JSON.stringify(data.data))
    }

    const hasChanges = computed(() => {
        return JSON.stringify(origin.value) !== JSON.stringify(edit.value);
    });
    function discardChanges(){
        edit.value = origin.value ? JSON.parse(JSON.stringify(origin.value)) :  null;
    }
    function updateEdit(newData: types.User[]){
        edit.value = newData;
    }

    return {
        origin,
        edit,
        setData,
        hasChanges,
        discardChanges,
        updateEdit
    }
});
export const useCustomersStore = defineStore('customers', () => {

    const origin = ref<types.Customer[] | null>(null)
    const edit = ref<types.Customer[] | null>(null)

    function setData(data:types.Response<types.Customer>){
        origin.value = data.data
        edit.value = JSON.parse(JSON.stringify(data.data))
    }

    const hasChanges = computed(() => {
        return JSON.stringify(origin.value) !== JSON.stringify(edit.value);
    });
    function discardChanges(){
        edit.value = origin.value ? JSON.parse(JSON.stringify(origin.value)) :  null;
    }
    function updateEdit(newData: types.Customer[]){
        edit.value = newData;
    }

    return {
        origin,
        edit,
        setData,
        hasChanges,
        discardChanges,
        updateEdit
    }
});
