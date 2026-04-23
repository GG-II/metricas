/**
 * Lazy Loading de Gráficos
 * Carga gráficos solo cuando están visibles en viewport
 */

(function() {
    'use strict';

    // Configuración
    const config = {
        rootMargin: '100px', // Cargar 100px antes de que sea visible
        threshold: 0.01
    };

    // Observer para detectar visibilidad
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const container = entry.target;
                loadChart(container);
                observer.unobserve(container); // Solo cargar una vez
            }
        });
    }, config);

    /**
     * Cargar gráfico
     */
    function loadChart(container) {
        const chartId = container.dataset.chartId;
        const chartConfig = container.dataset.chartConfig;

        if (!chartId || !chartConfig) {
            console.warn('Chart data missing', container);
            return;
        }

        try {
            const config = JSON.parse(chartConfig);

            // Mostrar loading
            container.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';

            // Simular delay mínimo para mostrar loader (opcional)
            setTimeout(() => {
                renderChart(container, chartId, config);
            }, 100);

        } catch (e) {
            console.error('Error parsing chart config', e);
            container.innerHTML = '<div class="alert alert-danger m-3">Error cargando gráfico</div>';
        }
    }

    /**
     * Renderizar gráfico (delegar a ApexCharts)
     */
    function renderChart(container, chartId, config) {
        // Aquí iría la lógica específica de renderizado
        // Por ahora, simplemente ejecutamos los scripts inline que ya existen

        container.classList.add('chart-loaded');

        // Trigger evento personalizado
        const event = new CustomEvent('chartLoaded', {
            detail: { chartId, config }
        });
        container.dispatchEvent(event);
    }

    /**
     * Inicializar lazy loading
     */
    function init() {
        // Observar todos los contenedores de gráficos
        const charts = document.querySelectorAll('[data-lazy-chart="true"]');

        charts.forEach(chart => {
            observer.observe(chart);
        });

        console.log(`Lazy loading initialized for ${charts.length} charts`);
    }

    /**
     * Auto-inicializar cuando DOM esté listo
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Exponer API global
    window.LazyCharts = {
        init,
        loadChart,
        observer
    };

})();
