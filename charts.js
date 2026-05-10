Chart.defaults.font.family = "'Open Sans', sans-serif";
Chart.defaults.color = 'rgba(217, 228, 255, 0.65)';
Chart.defaults.scale.grid.color = 'rgba(143, 169, 220, 0.1)';

// 1. Tendencia de Ingresos
new Chart(document.getElementById('tendenciaChart'), {
    type: 'line',
    data: {
        labels: chartData.str_meses,
        datasets: [{
            label: 'Ingresos ($)',
            data: chartData.str_ingresos_mes,
            borderColor: '#8a7cb8',
            backgroundColor: 'rgba(138, 124, 184, 0.12)',
            borderWidth: 3,
            pointBackgroundColor: '#0b1a33',
            pointBorderColor: '#8a7cb8',
            pointBorderWidth: 2,
            pointRadius: 4,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: function(value) { return '$' + value; } } } }
    }
});

// 2. Prepa vs Facultad 
new Chart(document.getElementById('nivelesChart'), {
    type: 'doughnut',
    data: {
        labels: ['Preparatoria', 'Facultad'],
        datasets: [{
            data: [chartData.prepa, chartData.facu],
            backgroundColor: ['rgba(143, 169, 220, 0.25)', 'rgba(138, 124, 184, 0.25)'],
            borderColor: ['#8fa9dc', '#8a7cb8'],
            borderWidth: 2,
            hoverOffset: 6
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { color: '#d9e4ff' } } } }
});

// 3. Desglose de Ingresos 
new Chart(document.getElementById('ingresosChart'), {
    type: 'bar',
    data: {
        labels: ['Separación', 'Pago 1', 'Pago 2', 'Pago 3', 'Pago 4', 'Pago 5'],
        datasets: [{
            label: 'Recaudado ($)',
            data: [
                chartData.ing.s, chartData.ing.p1, chartData.ing.p2,
                chartData.ing.p3, chartData.ing.p4, chartData.ing.p5
            ],
            backgroundColor: 'rgba(111, 199, 132, 0.2)',
            borderColor: '#6fc784',
            borderWidth: 2,
            borderRadius: 6
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: function(value) { return '$' + value; } } } } }
});

// 4. Facultades (GIGANTE con límite a 15)
new Chart(document.getElementById('facultadesChart'), {
    type: 'bar',
    data: {
        labels: chartData.str_fac_nombres,
        datasets: [{
            label: 'Alumnos',
            data: chartData.str_fac_cantidades,
            backgroundColor: 'rgba(143, 169, 220, 0.2)',
            borderColor: '#8fa9dc',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: { 
        indexAxis: 'y', responsive: true, maintainAspectRatio: false, 
        plugins: { legend: { display: false } },
        scales: { 
            x: { 
                max: 15,
                ticks: { stepSize: 1, precision: 0 } 
            }, 
            y: { 
                ticks: { font: { size: 11 }, color: 'rgba(217, 228, 255, 0.75)' } 
            } 
        } 
    } 
});

// 5. Estado General (Activos vs Inactivos)
new Chart(document.getElementById('estadoChart'), {
    type: 'pie',
    data: {
        labels: ['Activos', 'Inactivos'],
        datasets: [{
            data: [chartData.activos, chartData.inactivos],
            backgroundColor: ['rgba(111, 199, 132, 0.3)', 'rgba(224, 138, 154, 0.3)'],
            borderColor: ['#6fc784', '#e08a9a'],
            borderWidth: 2
        }]
    },
    options: { 
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, color: '#d9e4ff', padding: 8 } } }
    }
});

// 6. Distribución (Retencion)
new Chart(document.getElementById('distribucionChart'), {
    type: 'gauge',
    data: {
        labels: ['Tasa de Retención'],
        datasets: [{
            data: [chartData.retencion, 100 - chartData.retencion],
            backgroundColor: ['rgba(143, 169, 220, 0.3)', 'rgba(100, 100, 120, 0.15)'],
            borderColor: ['#8fa9dc', 'rgba(100, 100, 120, 0.3)'],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    }
});
