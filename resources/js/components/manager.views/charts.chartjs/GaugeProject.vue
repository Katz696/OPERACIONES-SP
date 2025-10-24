<script setup lang="ts">
import { computed } from "vue";
import { GaugeMeter, GaugeMeterOptions } from "vue3-gauge-chart";

const props = defineProps({
  value: { type: Number, default: 100 }, // Valor dinámico
});

// Valor máximo del gauge: si el valor real supera 100, lo usamos como máximo
const maxValue = computed(() => Math.max(props.value, 100));

// Calculamos delimitadores proporcionalmente
const arcDelimiters = computed(() => [
  Math.round(85/ maxValue.value * 100), // rojo
  Math.round(95/ maxValue.value * 100),
]);

// Labels dinámicos: siempre 0, 85, 95 y el máximo real
const rangeLabel = computed(() => [
  "0%",
  `${Math.round(maxValue.value).toString()}%`
]);

const options = computed<GaugeMeterOptions>(() => ({
  areaWidth: 400,
  hasNeedle: true,
  needleColor: "black",
  arcColors: ["rgb(255, 84, 84)", "rgb(239, 214, 19)", "rgb(61, 204, 91)"],
  arcDelimiters: arcDelimiters.value,
  arcLabels: ['85%', '95%'],
  rangeLabel: rangeLabel.value,
  needleValue: props.value,
  needleStartValue: props.value > 100 ? 100 : props.value,
  centralLabel:`${props.value}%`
}));
</script>

<template>
  <div class="flex justify-center items-center">
    <GaugeMeter :options="options" />
  </div>
</template>

<style scoped>
div {
  width: 100%;
  height: 250px;
}
</style>
