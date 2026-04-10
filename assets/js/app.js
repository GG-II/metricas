/**
 * JavaScript personalizado
 * Dashboard de Métricas IT
 */

// ==========================================
// CONFIGURACIÓN GLOBAL
// ==========================================
const BASE_URL = '/metricas';
const COLORS = {
    software: '#3b82f6',
    infraestructura: '#10b981',
    soporte: '#f59e0b',
    ciberseguridad: '#ef4444'
};

// ==========================================
// UTILIDADES
// ==========================================

/**
 * Formatear número con separadores de miles
 */
function formatNumber(num) {
    if (num === null || num === undefined) return '---';
    return Number(num).toLocaleString('es-GT');
}

/**
 * Formatear porcentaje
 */
function formatPercentage(num, decimals = 1) {
    if (num === null || num === undefined) return '---';
    return Number(num).toFixed(decimals) + '%';
}

/**
 * Calcular porcentaje de cambio
 */
function calcularCambio(valorActual, valorAnterior) {
    if (!valorAnterior || valorAnterior === 0) return null;
    return ((valorActual - valorAnterior) / valorAnterior) * 100;
}

/**
 * Obtener color del área actual
 */
function getAreaColor() {
    const activeTab = document.querySelector('.area-tab.active');
    if (activeTab) {
        return activeTab.getAttribute('data-area-color') || COLORS.software;
    }
    return COLORS.software;
}

/**
 * Actualizar CSS variable con color del área
 */
function updateAreaColor() {
    const color = getAreaColor();
    document.documentElement.style.setProperty('--area-color', color);
}

// ==========================================
// CAMBIO DE PERÍODO
// ==========================================
function cambiarPeriodo(periodo) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('periodo', periodo);
    window.location.search = urlParams.toString();
}

// ==========================================
// CARGA DE MÉTRICAS
// ==========================================
async function cargarMetricas(areaId, periodo) {
    try {
        const response = await fetch(`${BASE_URL}/api/get-area.php?area=${areaId}&periodo=${periodo}`);
        const data = await response.json();
        
        if (data.success) {
            return data;
        } else {
            throw new Error(data.error || 'Error al cargar métricas');
        }
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

// ==========================================
// RENDERIZADO DE MÉTRICAS
// ==========================================
function renderizarMetricas(metricas, comparativas = null) {
    if (!metricas || metricas.length === 0) {
        return `
            <div class="empty">
                <div class="empty-icon">
                    <i class="ti ti-database-off"></i>
                </div>
                <p class="empty-title">No hay métricas disponibles</p>
                <p class="empty-subtitle text-muted">
                    No se encontraron métricas para este período
                </p>
            </div>
        `;
    }
    
    let html = '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">';
    
    metricas.forEach(metrica => {
        const valor = formatearValor(metrica);
        const unidad = metrica.unidad ? ` <span class="metric-unit">${metrica.unidad}</span>` : '';
        const nota = metrica.nota ? `<div class="metric-note">${metrica.nota}</div>` : '';
        
        // Comparativa con período anterior (si existe)
        let comparativaHtml = '';
        if (comparativas && comparativas[metrica.id]) {
            const valorAnterior = comparativas[metrica.id];
            const cambio = calcularCambio(parseFloat(metrica.valor), parseFloat(valorAnterior));
            
            if (cambio !== null) {
                const clase = cambio > 0 ? 'positive' : (cambio < 0 ? 'negative' : 'neutral');
                const icono = cambio > 0 ? 'ti-trending-up' : (cambio < 0 ? 'ti-trending-down' : 'ti-minus');
                comparativaHtml = `
                    <div class="metric-comparison ${clase}">
                        <i class="ti ${icono}"></i>
                        <span>${cambio > 0 ? '+' : ''}${cambio.toFixed(1)}% vs anterior</span>
                    </div>
                `;
            }
        }
        
        html += `
            <div class="col">
                <div class="metric-card">
                    <div class="metric-icon">
                        <i class="ti ti-chart-bar"></i>
                    </div>
                    <div class="metric-label">${metrica.nombre}</div>
                    <div class="metric-value">${valor}${unidad}</div>
                    ${comparativaHtml}
                    ${nota}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    
    return html;
}

function formatearValor(metrica) {
    if (metrica.valor === null || metrica.valor === undefined) {
        return '---';
    }
    
    const valor = parseFloat(metrica.valor);
    
    if (metrica.tipo_valor === 'porcentaje' || metrica.tipo_valor === 'decimal') {
        return valor.toFixed(1);
    } else {
        return formatNumber(Math.round(valor));
    }
}

// ==========================================
// GRÁFICOS CON APEXCHARTS
// ==========================================
function crearGraficoBarras(elementId, titulo, categorias, series, color) {
    const options = {
        series: series,
        chart: {
            type: 'bar',
            height: 350,
            background: 'transparent',
            foreColor: '#94a3b8',
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 8
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            show: true,
            width: 2,
            colors: ['transparent']
        },
        xaxis: {
            categories: categorias,
            labels: {
                style: {
                    colors: '#94a3b8'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#94a3b8'
                }
            }
        },
        fill: {
            opacity: 1
        },
        tooltip: {
            theme: 'dark',
            y: {
                formatter: function (val) {
                    return formatNumber(val);
                }
            }
        },
        grid: {
            borderColor: 'rgba(255, 255, 255, 0.1)',
        },
        colors: color ? [color] : [getAreaColor()],
        title: {
            text: titulo,
            style: {
                fontSize: '16px',
                fontWeight: 600,
                color: '#e2e8f0'
            }
        }
    };
    
    const chart = new ApexCharts(document.querySelector(`#${elementId}`), options);
    chart.render();
    
    return chart;
}

function crearGraficoLinea(elementId, titulo, categorias, series, colores) {
    const options = {
        series: series,
        chart: {
            type: 'line',
            height: 350,
            background: 'transparent',
            foreColor: '#94a3b8',
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        xaxis: {
            categories: categorias,
            labels: {
                style: {
                    colors: '#94a3b8'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#94a3b8'
                }
            }
        },
        tooltip: {
            theme: 'dark',
            shared: true,
            intersect: false
        },
        grid: {
            borderColor: 'rgba(255, 255, 255, 0.1)',
        },
        colors: colores || [getAreaColor()],
        title: {
            text: titulo,
            style: {
                fontSize: '16px',
                fontWeight: 600,
                color: '#e2e8f0'
            }
        },
        legend: {
            labels: {
                colors: '#94a3b8'
            }
        }
    };
    
    const chart = new ApexCharts(document.querySelector(`#${elementId}`), options);
    chart.render();
    
    return chart;
}

// ==========================================
// INICIALIZACIÓN
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar color del área
    updateAreaColor();
    
    // Inicializar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// ==========================================
// EXPORTAR FUNCIONES GLOBALES
// ==========================================
window.cargarMetricas = cargarMetricas;
window.renderizarMetricas = renderizarMetricas;
window.crearGraficoBarras = crearGraficoBarras;
window.crearGraficoLinea = crearGraficoLinea;
window.cambiarPeriodo = cambiarPeriodo;
window.formatNumber = formatNumber;
window.formatPercentage = formatPercentage;