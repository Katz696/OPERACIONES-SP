import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const  useAgreementsStore = defineStore('agreements',()=> {
  const editable = ref<any[]>([]);
  const hasChanges = ref(false);

  function setAgreements(data: any[]) {
    editable.value = data;
    hasChanges.value = false; // recién cargados no hay cambios
  }

  function addAgreement(agreement: any) {
    editable.value.push(agreement);
    hasChanges.value = true;
  }

  function removeAgreement(id: number | null) {
    editable.value = editable.value.filter(a => a.id !== id);
    hasChanges.value = true;
  }

  function markChanges() {
    hasChanges.value = true;
  }

  return {
    editable,
    hasChanges,
    setAgreements,
    addAgreement,
    removeAgreement,
    markChanges,
  };
});
