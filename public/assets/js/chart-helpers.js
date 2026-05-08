/**
 * Chart Helpers - Funciones auxiliares para gráficos
 * Formateo de números y utilidades comunes
 */

/**
 * Formatea un número con separadores de miles
 * @param {number} val - Valor a formatear
 * @param {number} decimals - Número de decimales (default: 0)
 * @param {string} locale - Locale para formato (default: 'es-GT')
 * @returns {string} Número formateado
 */
function formatNumber(val, decimals = 0, locale = 'es-GT') {
    if (val === null || val === undefined || isNaN(val)) {
        return '0';
    }

    return Number(val.toFixed(decimals)).toLocaleString(locale, {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

/**
 * Formatea un valor para tooltips de ApexCharts
 * @param {number} val - Valor
 * @param {string} unidad - Unidad de medida (opcional)
 * @param {number} decimals - Decimales (default: 0)
 * @returns {string} Valor formateado con unidad
 */
function formatChartValue(val, unidad = '', decimals = 0) {
    const formatted = formatNumber(val, decimals);
    return unidad ? `${formatted} ${unidad}` : formatted;
}

/**
 * Formatea un porcentaje
 * @param {number} val - Valor del porcentaje
 * @param {number} decimals - Decimales (default: 1)
 * @returns {string} Porcentaje formateado
 */
function formatPercentage(val, decimals = 1) {
    return formatNumber(val, decimals) + '%';
}

/**
 * Abrevia números grandes (K, M, B)
 * @param {number} val - Valor a abreviar
 * @param {number} decimals - Decimales (default: 1)
 * @returns {string} Número abreviado
 */
function abbreviateNumber(val, decimals = 1) {
    if (val === null || val === undefined || isNaN(val)) {
        return '0';
    }

    const absVal = Math.abs(val);
    const sign = val < 0 ? '-' : '';

    if (absVal >= 1e9) {
        return sign + formatNumber(absVal / 1e9, decimals) + 'B';
    }
    if (absVal >= 1e6) {
        return sign + formatNumber(absVal / 1e6, decimals) + 'M';
    }
    if (absVal >= 1e3) {
        return sign + formatNumber(absVal / 1e3, decimals) + 'K';
    }

    return sign + formatNumber(absVal, decimals);
}

/**
 * Detecta si el sistema está en modo oscuro
 * @returns {boolean} true si es modo oscuro
 */
function isDarkTheme() {
    return document.documentElement.getAttribute('data-bs-theme') === 'dark';
}

/**
 * Obtiene el color de texto según el tema
 * @returns {string} Color hexadecimal para texto
 */
function getThemeTextColor() {
    return isDarkTheme() ? '#fff' : '#1e293b';
}

/**
 * Obtiene el color secundario según el tema
 * @returns {string} Color hexadecimal para texto secundario
 */
function getThemeSecondaryColor() {
    return '#94a3b8';
}

// Exportar para uso global
window.ChartHelpers = {
    formatNumber,
    formatChartValue,
    formatPercentage,
    abbreviateNumber,
    isDarkTheme,
    getThemeTextColor,
    getThemeSecondaryColor
};
