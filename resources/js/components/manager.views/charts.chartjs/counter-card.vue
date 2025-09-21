<template>
  <!-- Muestra el número en el título de la tarjeta -->
  <n-card :title="counter">
    <!-- El contenido de la tarjeta -->
    {{ title }}
  </n-card>
</template>

<script setup>
defineProps({
  title: String,
  counter: String,
});
</script>

<style scoped>
.n-card {
  max-width: 200px;
}
</style>
