import { Chart } from "chart.js";
import "chart.js/dist/Chart.css";

document.querySelectorAll("canvas[data-chart]").forEach((element) => {
  const ctx = element.getContext("2d");
  const data = JSON.parse(element.dataset.chartData);
  new Chart(ctx, {
    type: "line",
    data: {
      labels: Object.keys(data),
      datasets: [
        {
          label: element.dataset.chartLabel,
          borderColor: "#f56e4e",
          data: Object.values(data),
          cubicInterpolationMode: "monotone",
        },
      ],
    },
    options: {
      scales: {
        yAxes: [
          {
            ticks: {
              beginAtZero: true,
            },
          },
        ],
      },
    },
  });
});
