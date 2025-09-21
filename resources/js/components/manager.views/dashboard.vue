<template>
  <div class="p-4">
    <!-- cards counters -->
    <n-flex>
      <card title="Prueba" counter="94" />
    </n-flex>
    <div class="grid grid-cols-3 gap-4">
      <div v-for="(phase, index) in store.editable.project.phases" :key="index">
        <GaugePhase
            :label="phase.data.title"
            :percent_planned="phase.data.percentage_planned"
            :percent_progress="0"
            :percent="phase.data.percentage" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useProjectStore } from "@/composables/useProjectStore";
import { watch } from "vue";
import GaugePhase from "./charts.chartjs/GaugePhase.vue";
import card from "@/components/manager.views/charts.chartjs/counter-card.vue";
const store = useProjectStore();

watch(
  () => store.editable,
  (newVal) => {
    store.value = newVal.value;
  }
);
</script>
