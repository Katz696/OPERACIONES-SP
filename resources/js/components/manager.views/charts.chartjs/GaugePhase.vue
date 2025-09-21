<template>
  <div class="relative w-full h-52">
    <canvas ref="canvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onBeforeUnmount } from "vue";
import { Chart, DoughnutController, ArcElement, Tooltip, Legend, Title } from "chart.js";

Chart.register(DoughnutController, ArcElement, Tooltip, Legend, Title);

const props = defineProps({
  label: { type: String, required: true },
  percent: { type: Number, required: true },
  percent_planned: { type: Number, required: true },
  percent_progress: { type: Number, required: true },
  target: { type: Number, default: 100 },
});

const canvas = ref(null);
let chartInstance = null;

const renderChart = () => {
  if (chartInstance) chartInstance.destroy();

  chartInstance = new Chart(canvas.value, {
    type: "doughnut",
    data: {
      datasets: [
        {
          percent: props.percent_planned,
          data: [props.percent_planned, props.target - props.percent_planned],
          backgroundColor: ["#3b82f6", "#DDDDDD"], // azul planned
          borderWidth: 0,
        },
        {
          percent: props.percent_progress,
          data: [props.percent_progress, props.target - props.percent_progress],
          backgroundColor: ["#10b981", "#DDDDDD"], // verde progress
          borderWidth: 0,
        },
        {
          percent: props.percent,
          data: [props.percent, props.target - props.percent],
          backgroundColor: ["#f59e0b", "#DDDDDD"], // amarillo actual
          borderWidth: 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: "45%",
      rotation: 270,
      circumference: 180,
      plugins: {
        title: {
          display: true,
          text: props.label,
          font: {
            size: 16,
            weight: "bold",
          },
          padding: { top: 5, bottom: 5 },
        },
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function (tooltipItem) {
              const datasetIndex = tooltipItem.datasetIndex;
              const value = tooltipItem.raw;
              switch (datasetIndex) {
                case 0:
                  return `Planeado: ${value}%`;
                case 1:
                  return `Completado + progreso: ${value}%`;
                case 2:
                  return `Completado: ${value}%`;
                default:
                  return `${value}%`;
              }
            },
          },
        },
      },
      animation: { duration: 1200 },
    },
    plugins: [
      {
        // porcentaje en el centro
        beforeDraw(chart) {
          const { width, height, ctx } = chart;
          ctx.save();
          ctx.font = `${(height / 200).toFixed(2)}em sans-serif`;
          ctx.fillStyle = "#9b9b9b";
          ctx.textBaseline = "middle";
          const text = `${props.percent}%`;
          const textX = Math.round((width - ctx.measureText(text).width) / 2);
          const textY = height / 1.2;
          ctx.fillText(text, textX, textY);
          ctx.restore();
        },
      },
      {
        // aguja
        id: "gaugeNeedle",
        afterDatasetsDraw(chart) {
          const { ctx, data } = chart;
          ctx.save();
          const needleValue = data.datasets[2].percent;
          const xCenter = chart.getDatasetMeta(0).data[0].x;
          const yCenter = chart.getDatasetMeta(0).data[0].y;
          const outerRadius = chart.getDatasetMeta(1).data[0].innerRadius;

          const divisor = data.datasets[0].data[0] === 0 ? 100 : data.datasets[0].data[0];
          const ValueCircumference =
            data.datasets[0].data[0] === 0
              ? chart.getDatasetMeta(0).data[1].circumference
              : chart.getDatasetMeta(0).data[0].circumference;

          let circumference = (ValueCircumference / Math.PI / divisor) * needleValue;
          const needleValueAngle = circumference + 1.5;

          ctx.translate(xCenter, yCenter);
          ctx.rotate(Math.PI * needleValueAngle);

          ctx.beginPath();
          ctx.moveTo(-2, 0);
          ctx.lineTo(0, -outerRadius);
          ctx.lineTo(2, 0);
          ctx.fill();

          ctx.beginPath();
          ctx.arc(0, 0, 5, 0, Math.PI * 2, false);
          ctx.fill();

          ctx.restore();
        },
      },
    ],
  });
};

onMounted(renderChart);
watch(() => props.percent_planned, renderChart);
watch(() => props.percent_progress, renderChart);
watch(() => props.percent, renderChart);
watch(() => props.label, renderChart);
onBeforeUnmount(() => chartInstance?.destroy());
</script>
